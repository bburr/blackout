<?php declare(strict_types=1);

namespace App\State;

use App\Jobs\DetermineDealer;
use App\Jobs\StartRound;
use App\Models\Game;
use App\State\Handlers\GameStateCacheHandlerInterface;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;

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

    protected CardShoeState $shoe;

    /** @var Collection<int, PlayerState> */
    protected Collection $players;

    /** @var Collection<int, RoundScoreState>  */
    protected Collection $previousRounds;

    public function __construct(protected Game $game, protected ?GameSettings $gameSettings)
    {
        $this->players = new Collection();
        $this->previousRounds = new Collection();

        $this->cacheHandler = app(GameStateCacheHandlerInterface::class, ['gameKey' => $this->game->getKey()]);

        // check for existing game
        $this->cacheHandler->cacheHas(self::GAME_STATE_CACHE_KEY)
            ? $this->loadGame()
            : $this->initGame();
    }

    public function addPreviousRound(RoundScoreState $roundScoreState): void
    {
        $this->previousRounds->add($roundScoreState);
    }

    public function advanceBettingPlayerIndex(): void
    {
        $bettingPlayerIndex = $this->getCurrentRound()->getNextPlayerIndexToBet();

        $this->getCurrentRound()->setNextPlayerIndexToBet($this->advancePlayerIndexUntilDealer($bettingPlayerIndex));
    }

    public function advanceDealerIndex(): void
    {
        $this->dealerIndex = $this->getPlayerIndexAfter($this->dealerIndex);
    }

    public function advancePlayerIndex(): void
    {
        $playerIndex = $this->getCurrentRound()->getNextPlayerIndexToPlay();

        $this->getCurrentRound()->setNextPlayerIndexToPlay($this->advancePlayerIndexUntilDealer($playerIndex));
    }

    protected function advancePlayerIndexUntilDealer(int $playerIndex): int
    {
        if ($playerIndex === $this->dealerIndex) {
            $playerIndex = -1;
        }

        if ($playerIndex >= 0) {
            $playerIndex++;
        }

        if ($playerIndex >= $this->players->count()) {
            $playerIndex = 0;
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

    public function getGameSettings(): GameSettings
    {
        return $this->gameSettings ?? $this->gameSettings = new GameSettings();
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

    protected function initGame(): void
    {
        foreach ($this->game->getUsers() as $user) {
            $this->players->add(new PlayerState($user));
        }

        $this->dealerIndex = Bus::dispatch(new DetermineDealer($this->players->keys()->toArray()));

        Bus::dispatch(new StartRound($this, 1, $this->getGameSettings()->getStartingNumCards(), true));
    }

    public function jsonSerialize()
    {
        return [
            'game_id' => $this->game->getKey(),
            'dealer_index' => $this->getDealerIndex(),
            'settings' => $this->getGameSettings()->jsonSerialize(),
        ];
    }

    protected function loadGame(): void
    {
        $gameStateData = $this->cacheHandler->cacheGet(self::GAME_STATE_CACHE_KEY);
        $this->dealerIndex = $gameStateData['dealer_index'];
        // todo load settings
        $this->gameSettings = (new GameSettings());

        foreach ($this->cacheHandler->cacheGet(self::PLAYERS_CACHE_KEY) as $playerData) {
            $this->players->add(PlayerState::loadFromSaveData($playerData));
        }

        $cardShoeData = $this->cacheHandler->cacheGet(self::CARD_SHOE_CACHE_KEY);
        $this->shoe = CardShoeState::loadFromSaveData($cardShoeData);

        $currentRoundData = $this->cacheHandler->cacheGet(self::CURRENT_ROUND_CACHE_KEY);
        $this->currentRound = RoundState::loadFromSaveData($currentRoundData);

        // todo load previousRounds
    }

    // todo move to job
    public function makePlayForNextPlayer(CardState $cardState): void
    {
        /** @var PlayerState $player */
        $player = $this->getPlayerAtIndex($this->getCurrentRound()->getNextPlayerIndexToPlay());

        $index = $player->getHand()->search($cardState);

        if ($index === false) {
            // todo exception
            throw new \LogicException('Player does not have that card');
        }

        // todo business logic for valid card play
        $this->getCurrentRound()->makePlayForNextPlayer($cardState);
        $player->getHand()->forget($index);
        $this->advancePlayerIndex();
    }

    public function save(): void
    {
        $this->cacheHandler->cachePut(self::GAME_STATE_CACHE_KEY, $this);
        $this->cacheHandler->cachePut(self::PLAYERS_CACHE_KEY, $this->players);
        $this->cacheHandler->cachePut(self::CARD_SHOE_CACHE_KEY, $this->shoe);
        $this->cacheHandler->cachePut(self::CURRENT_ROUND_CACHE_KEY, $this->currentRound);
        $this->cacheHandler->cachePut(self::PREVIOUS_ROUNDS_CACHE_KEY, $this->previousRounds);
    }

    public function setCurrentRound(RoundState $roundState): void
    {
        $this->currentRound = $roundState;
    }

    public function setShoe(CardShoeState $cardShoeState): void
    {
        $this->shoe = $cardShoeState;
    }
}
