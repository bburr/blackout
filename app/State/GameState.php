<?php declare(strict_types=1);

namespace App\State;

use App\Models\Game;
use App\Models\User;
use App\State\Actions\DetermineDealer;
use Generator;
use Illuminate\Support\Collection;
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

    protected function advanceDealerIndex()
    {
        $this->dealerIndex++;

        if ($this->dealerIndex >= $this->players->count()) {
            $this->dealerIndex = 0;
        }
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

    protected function initGame()
    {
        foreach ($this->game->getUsers() as $user) {
            $this->players->add(new PlayerState($user));
        }

        $this->dealerIndex = (new DetermineDealer())($this->players->keys()->toArray());

        $this->startRound(1, $this->gameSettings->getStartingNumCards(), true);
    }

    public function jsonSerialize()
    {
        return [
            'game_id' => $this->game->getKey(),
            'dealer_index' => $this->dealerIndex,
            'settings' => $this->gameSettings,
        ];
    }

    protected function loadGame()
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

    public function nextRound()
    {
        // todo move method to action(s)?
        $currentRound = $this->currentRound;

        // todo score current round and save to $previousRounds
        $this->previousRounds->add(new RoundScoreState());

        if ($currentRound->isNumCardsAscending()) {
            $numCards = $currentRound->getNumCards() + 1;

            if ($numCards === $this->gameSettings->getMaxNumCards()) {
                $isNumCardsAscending = false;
            }
        }
        else {
            $numCards = $currentRound->getNumCards() - 1;

            if ($numCards < $this->gameSettings->getEndingNumCards()) {
                throw new \LogicException('Game is over, cannot advance past this round');
            }
        }

        $this->advanceDealerIndex();

        $this->startRound($currentRound->getRoundNumber() + 1, $numCards, $isNumCardsAscending ?? $currentRound->isNumCardsAscending());
    }

    public function save()
    {
        $this->cachePut(self::GAME_STATE_CACHE_KEY, $this);
        $this->cachePut(self::PLAYERS_CACHE_KEY, $this->players);
        $this->cachePut(self::CARD_SHOE_CACHE_KEY, $this->shoe);
        $this->cachePut(self::CURRENT_ROUND_CACHE_KEY, $this->currentRound);
        $this->cachePut(self::PREVIOUS_ROUNDS_CACHE_KEY, $this->previousRounds);
    }

    protected function startRound(int $roundNumber, int $numCards, bool $isNumCardsAscending)
    {
        // todo move method to action?
        $this->currentRound = new RoundState($roundNumber, $numCards, $isNumCardsAscending);

        $this->shoe = new CardShoeState($this->gameSettings->getNumDecks());
    }
}
