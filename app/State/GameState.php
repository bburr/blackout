<?php declare(strict_types=1);

namespace App\State;

use App\Jobs\DetermineDealer;
use App\Jobs\StartRound;
use App\Models\Game;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class GameState extends AbstractState
{
    protected const CARD_SHOE_CACHE_KEY = 'game-shoe-';
    protected const CURRENT_ROUND_CACHE_KEY = 'game-current-round-';
    protected const GAME_STATE_CACHE_KEY = 'game-state-';
    protected const PLAYERS_CACHE_KEY = 'game-players-';
    protected const PREVIOUS_ROUNDS_CACHE_KEY = 'game-previous-rounds-';

    protected RoundState $currentRound;

    protected int $dealerIndex;

    protected CardShoeState $shoe;

    /** @var Collection<PlayerState> */
    protected Collection $players;

    /** @var Collection<RoundScoreState>  */
    protected Collection $previousRounds;

    public function __construct(protected Game $game, protected ?GameSettings $gameSettings)
    {
        $this->players = collect();
        $this->previousRounds = collect();

        // check for existing game
        Cache::has($this->cacheKey(self::GAME_STATE_CACHE_KEY))
            ? $this->loadGame()
            : $this->initGame();
    }

    public function addPreviousRound(RoundScoreState $roundScoreState): void
    {
        $this->previousRounds->add($roundScoreState);
    }

    public function advanceBettingPlayerIndex(): void
    {
        // todo combine logic with player index logic
        $bettingPlayerIndex = $this->getCurrentRound()->getNextPlayerIndexToBet();

        if ($bettingPlayerIndex === $this->dealerIndex) {
            $bettingPlayerIndex = -1;
        }

        if ($bettingPlayerIndex >= 0) {
            $bettingPlayerIndex++;
        }

        if ($bettingPlayerIndex >= $this->players->count()) {
            $bettingPlayerIndex = 0;
        }

        $this->getCurrentRound()->setNextPlayerIndexToBet($bettingPlayerIndex);
    }

    public function advanceDealerIndex(): void
    {
        $this->dealerIndex = $this->getPlayerIndexAfter($this->dealerIndex);
    }

    public function advancePlayerIndex(): void
    {
        $playerIndex = $this->getCurrentRound()->getNextPlayerIndexToPlay();

        if ($playerIndex === $this->dealerIndex) {
            $playerIndex = -1;
        }

        if ($playerIndex >= 0) {
            $playerIndex++;
        }

        if ($playerIndex >= $this->players->count()) {
            $playerIndex = 0;
        }

        $this->getCurrentRound()->setNextPlayerIndexToPlay($playerIndex);
    }

    protected function cacheKey(string $cacheKey): string
    {
        return $cacheKey . $this->game->getKey();
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
        return $this->players[$this->dealerIndex];
    }

    public function getDealerIndex(): int
    {
        return $this->dealerIndex;
    }

    public function getGameSettings(): GameSettings
    {
        return $this->gameSettings;
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
            yield $this->players->get($i);
        }

        for ($i = 0; $i <= $this->dealerIndex; $i++) {
            yield $this->players->get($i);
        }
    }

    protected function initGame(): void
    {
        foreach ($this->game->getUsers() as $user) {
            $this->players->add(new PlayerState($user));
        }

        $this->dealerIndex = Bus::dispatch(new DetermineDealer($this->players->keys()->toArray()));

        Bus::dispatch(new StartRound($this, 1, $this->gameSettings->getStartingNumCards(), true));
    }

    public function jsonSerialize()
    {
        return [
            'game_id' => $this->game->getKey(),
            'dealer_index' => $this->dealerIndex,
            'settings' => $this->gameSettings,
        ];
    }

    protected function loadGame(): void
    {
        $gameStateData = $this->cacheGet(self::GAME_STATE_CACHE_KEY);
        $this->dealerIndex = $gameStateData['dealer_index'];
        // todo load settings
        $this->gameSettings = (new GameSettings());

        foreach ($this->cacheGet(self::PLAYERS_CACHE_KEY) as $playerData) {
            $this->players->add(PlayerState::loadFromSaveData($playerData));
        }

        $cardShoeData = $this->cacheGet(self::CARD_SHOE_CACHE_KEY);
        $this->shoe = CardShoeState::loadFromSaveData($cardShoeData);

        $currentRoundData = $this->cacheGet(self::CURRENT_ROUND_CACHE_KEY);
        $this->currentRound = RoundState::loadFromSaveData($currentRoundData);

        // todo load previousRounds
    }

    public function makeBetForNextPlayer(int $bet)
    {
        $this->getCurrentRound()->makeBetForNextPlayer($bet);
        $this->advanceBettingPlayerIndex();
    }

    public function makePlayForNextPlayer(CardState $cardState)
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
        $this->cachePut(self::GAME_STATE_CACHE_KEY, $this);
        $this->cachePut(self::PLAYERS_CACHE_KEY, $this->players);
        $this->cachePut(self::CARD_SHOE_CACHE_KEY, $this->shoe);
        $this->cachePut(self::CURRENT_ROUND_CACHE_KEY, $this->currentRound);
        $this->cachePut(self::PREVIOUS_ROUNDS_CACHE_KEY, $this->previousRounds);
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
