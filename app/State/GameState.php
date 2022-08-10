<?php declare(strict_types=1);

namespace App\State;

use App\Exceptions\InvalidTrickNumberSettingsException;
use App\Jobs\DetermineDealer;
use App\Jobs\StartRound;
use App\Models\Game;
use App\State\Handlers\GameStateCacheHandlerInterface;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;

/**
 * @phpstan-import-type InputGameSettings from GameSettings
 * @phpstan-import-type SerializedGameSettings from GameSettings
 * @phpstan-type SerializedGameState array{game_id: string, dealer_index: int, leading_player_index: int, settings: SerializedGameSettings}
 */
class GameState extends AbstractState
{
    const CARD_SHOE_CACHE_KEY = 'game-shoe-';
    const CURRENT_ROUND_CACHE_KEY = 'game-current-round-';
    const GAME_STATE_CACHE_KEY = 'game-state-';
    const PLAYERS_CACHE_KEY = 'game-players-';
    const PREVIOUS_ROUNDS_CACHE_KEY = 'game-previous-rounds-';

    protected GameStateCacheHandlerInterface $cacheHandler;

    protected RoundState $currentRound;

    protected int $dealerIndex;

    protected GameSettings $gameSettings;

    protected int $leadingPlayerIndex;

    protected CardShoeState $shoe;

    /** @var Collection<int, PlayerState> */
    protected Collection $players;

    /** @var Collection<int, RoundScoreState>  */
    protected Collection $previousRoundScores;

    /**
     * @phpstan-param InputGameSettings $settings
     * @throws InvalidTrickNumberSettingsException
     */
    public function __construct(protected Game $game, array $settings = [])
    {
        $this->players = new Collection();
        $this->previousRoundScores = new Collection();

        $this->cacheHandler = app(GameStateCacheHandlerInterface::class, ['gameKey' => $this->game->getKey()]);

        // check for existing game
        $this->cacheHandler->cacheHas(self::GAME_STATE_CACHE_KEY)
            ? $this->loadGame()
            : $this->initGame($settings);
    }

    public function addPreviousRoundScore(RoundScoreState $roundScoreState): void
    {
        $this->previousRoundScores->add($roundScoreState);
    }

    public function advanceDealerIndex(): void
    {
        $this->dealerIndex = $this->getPlayerIndexAfter($this->dealerIndex);
    }

    // todo move leading player into RoundState?
    public function advancePlayerIndexUntilLeadingPlayer(int $playerIndex): int
    {
        if ($playerIndex >= 0) {
            $playerIndex++;
        }

        if ($playerIndex >= $this->players->count()) {
            $playerIndex = 0;
        }

        if ($playerIndex === $this->leadingPlayerIndex) {
            $playerIndex = -1;
        }

        return $playerIndex;
    }

    public function getCardShoeState(): CardShoeState
    {
        return $this->shoe;
    }

    public function getCurrentRound(): RoundState
    {
        return $this->currentRound;
    }

    public function getDealer(): PlayerState
    {
        /** @phpstan-ignore-next-line */
        return $this->players[$this->dealerIndex];
    }

    public function getDealerIndex(): int
    {
        return $this->dealerIndex;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function getGameSettings(): GameSettings
    {
        return $this->gameSettings;
    }

    public function getLeadingPlayerIndex(): int
    {
        return $this->leadingPlayerIndex;
    }

    public function getPlayerAtIndex(int $index): ?PlayerState
    {
        return $this->players->get($index);
    }

    public function getPlayerIndexAfter(int $index): int
    {
        $index++;

        if ($index >= $this->players->count()) {
            $index = 0;
        }

        return $index;
    }

    /**
     * @return Collection<int, PlayerState>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @return Generator<PlayerState>
     */
    public function getPlayersInDealingOrder(): Generator
    {
        for ($i = $this->dealerIndex + 1; $i < $this->players->count(); $i++) {
            /** @phpstan-ignore-next-line  */
            yield $this->players->get($i);
        }

        for ($i = 0; $i <= $this->dealerIndex; $i++) {
            /** @phpstan-ignore-next-line  */
            yield $this->players->get($i);
        }
    }

    /**
     * @return Collection<int, RoundScoreState>
     */
    public function getPreviousRoundScores(): Collection
    {
        return $this->previousRoundScores;
    }

    /**
     * @phpstan-param InputGameSettings $settings
     * @throws InvalidTrickNumberSettingsException
     */
    protected function initGame(array $settings): void
    {
        foreach ($this->game->getUsers() as $user) {
            $this->players->add(new PlayerState($user));
        }

        $this->gameSettings = new GameSettings($this->players->count(), $settings);

        $this->dealerIndex = Bus::dispatch(new DetermineDealer($this->players->keys()->toArray()));
        $this->leadingPlayerIndex = $this->getPlayerIndexAfter($this->dealerIndex);

        Bus::dispatch(new StartRound($this, 1, $this->getGameSettings()->getStartingNumTricks(), true));
    }

    /**
     * @phpstan-return SerializedGameState
     */
    public function jsonSerialize(): array
    {
        return [
            'game_id' => $this->game->getKey(),
            'dealer_index' => $this->getDealerIndex(),
            'leading_player_index' => $this->leadingPlayerIndex,
            'settings' => $this->getGameSettings()->jsonSerialize(),
        ];
    }

    /**
     * @throws InvalidTrickNumberSettingsException
     */
    protected function loadGame(): void
    {
        $gameStateData = $this->cacheHandler->cacheGet(self::GAME_STATE_CACHE_KEY);
        $this->dealerIndex = $gameStateData['dealer_index'];
        $this->leadingPlayerIndex = $gameStateData['leading_player_index'];

        foreach ($this->cacheHandler->cacheGet(self::PLAYERS_CACHE_KEY) as $playerData) {
            $this->players->add(PlayerState::loadFromSaveData($playerData));
        }

        $this->gameSettings = GameSettings::loadFromSaveData($this->players->count(), $gameStateData['settings']);

        $cardShoeData = $this->cacheHandler->cacheGet(self::CARD_SHOE_CACHE_KEY);
        $this->shoe = CardShoeState::loadFromSaveData($cardShoeData);

        $currentRoundData = $this->cacheHandler->cacheGet(self::CURRENT_ROUND_CACHE_KEY);
        $this->currentRound = RoundState::loadFromSaveData($currentRoundData);

        foreach ($this->cacheHandler->cacheGet(self::PREVIOUS_ROUNDS_CACHE_KEY) as $roundScoreStateData) {
            $this->previousRoundScores->add(RoundScoreState::loadFromSaveData($roundScoreStateData));
        }
    }

    public function save(): void
    {
        $this->cacheHandler->cachePut(self::GAME_STATE_CACHE_KEY, $this);
        $this->cacheHandler->cachePut(self::PLAYERS_CACHE_KEY, $this->players);
        $this->cacheHandler->cachePut(self::CARD_SHOE_CACHE_KEY, $this->shoe);
        $this->cacheHandler->cachePut(self::CURRENT_ROUND_CACHE_KEY, $this->currentRound);
        $this->cacheHandler->cachePut(self::PREVIOUS_ROUNDS_CACHE_KEY, $this->previousRoundScores);
    }

    public function setCurrentRound(RoundState $roundState): void
    {
        $this->currentRound = $roundState;
    }

    public function setLeadingPlayerIndex(int $index): void
    {
        $this->leadingPlayerIndex = $index;
    }

    public function setShoe(CardShoeState $cardShoeState): void
    {
        $this->shoe = $cardShoeState;
    }
}
