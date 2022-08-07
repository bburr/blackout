<?php declare(strict_types=1);

namespace App\State;

use App\Exceptions\InvalidBetAmountException;
use App\State\Collections\TrickCollection;

/**
 * @phpstan-consistent-constructor
 * @phpstan-import-type SerializedCardState from CardState
 * @phpstan-import-type SerializedTrickState from TrickState
 * @phpstan-import-type SerializedTrickCollection from TrickCollection
 * @phpstan-type SerializedRoundConfig array{round_number: int, num_tricks: int, is_num_tricks_ascending: bool, next_player_index_to_bet: int, next_player_index_to_play: int}
 * @phpstan-type SerializedRoundState array{config: SerializedRoundConfig, trump_card: SerializedCardState|null, bets: int[], previous_tricks: SerializedTrickCollection, current_trick: SerializedTrickState}
 */
class RoundState extends AbstractState
{
    /**
     * @var int[]
     */
    protected array $bets = [];

    protected TrickState $currentTrick;

    protected TrickCollection $previousTricks;

    protected ?CardState $trumpCard = null;

    public function __construct(protected int $roundNumber, protected int $numTricks, protected bool $isNumTricksAscending, protected int $nextPlayerIndexToBet, protected int $nextPlayerIndexToPlay)
    {
        $this->currentTrick = new TrickState();
        $this->previousTricks = new TrickCollection();
    }

    public function addPreviousTrick(TrickState $trickState): void
    {
        $this->previousTricks->add($trickState);
    }

    /**
     * @return int[]
     */
    public function getBets(): array
    {
        return $this->bets;
    }

    public function getCurrentTrick(): TrickState
    {
        return $this->currentTrick;
    }

    public function getNextPlayerIndexToBet(): int
    {
        return $this->nextPlayerIndexToBet;
    }

    public function getNextPlayerIndexToPlay(): int
    {
        return $this->nextPlayerIndexToPlay;
    }

    public function getNumTricks(): int
    {
        return $this->numTricks;
    }

    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    public function getTrumpCard(): ?CardState
    {
        return $this->trumpCard;
    }

    public function isBettingDone(): bool
    {
        return $this->nextPlayerIndexToBet < 0;
    }

    public function isNumTricksAscending(): bool
    {
        return $this->isNumTricksAscending;
    }

    public function isRoundDone(): bool
    {
        return $this->previousTricks->count() === $this->numTricks;
    }

    /**
     * @phpstan-return SerializedRoundState
     */
    public function jsonSerialize(): array
    {
        return [
            'config' => [
                'round_number' => $this->roundNumber,
                'num_tricks' => $this->numTricks,
                'is_num_tricks_ascending' => $this->isNumTricksAscending,
                'next_player_index_to_bet' => $this->nextPlayerIndexToBet,
                'next_player_index_to_play' => $this->nextPlayerIndexToPlay,
            ],
            'trump_card' => $this->trumpCard?->jsonSerialize(),
            'bets' => $this->bets,
            'previous_tricks' => $this->previousTricks->jsonSerialize(),
            'current_trick' => $this->currentTrick->jsonSerialize(),
        ];
    }

    /**
     * @param array $roundData
     * @phpstan-param SerializedRoundState $roundData
     * @return static
     */
    public static function loadFromSaveData(array $roundData): static
    {
        $round = (new static(
            $roundData['config']['round_number'],
            $roundData['config']['num_tricks'],
            $roundData['config']['is_num_tricks_ascending'],
            $roundData['config']['next_player_index_to_bet'],
            $roundData['config']['next_player_index_to_play']
        ));

        if (isset($roundData['trump_card'])) {
            $round->setTrumpCard(new CardState($roundData['trump_card']['suit'], $roundData['trump_card']['value']));
        }

        $round->setBets($roundData['bets']);

        $round->setPreviousTricksFromArray($roundData['previous_tricks']);
        $round->setCurrentTrickFromArray($roundData['current_trick']);

        return $round;
    }

    /**
     * @throws InvalidBetAmountException
     */
    public function makeBetForNextPlayer(int $bet): void
    {
        if ($bet < 0 || $bet > $this->numTricks) {
            throw new InvalidBetAmountException();
        }

        $this->bets[$this->nextPlayerIndexToBet] = $bet;
    }

    public function makePlayForNextPlayer(CardState $cardState): void
    {
        $this->currentTrick->makePlayForPlayer($this->nextPlayerIndexToPlay, $cardState);
    }

    /**
     * @param int[] $bets
     * @return void
     */
    public function setBets(array $bets): void
    {
        $this->bets = $bets;
    }

    /**
     * @param array $trickData
     * @phpstan-param SerializedTrickState $trickData
     * @return void
     */
    public function setCurrentTrickFromArray(array $trickData): void
    {
        $this->currentTrick = TrickState::loadFromSaveData($trickData);
    }

    public function setNextPlayerIndexToBet(int $index): void
    {
        $this->nextPlayerIndexToBet = $index;
    }

    public function setNextPlayerIndexToPlay(int $index): void
    {
        $this->nextPlayerIndexToPlay = $index;
    }

    /**
     * @param array $tricksData
     * @phpstan-param SerializedTrickState[] $tricksData
     * @return void
     */
    public function setPreviousTricksFromArray(array $tricksData): void
    {
        $this->previousTricks = new TrickCollection();

        foreach ($tricksData as $trickData) {
            $this->previousTricks->add(TrickState::loadFromSaveData($trickData));
        }
    }

    public function setTrumpCard(CardState $cardState): void
    {
        $this->trumpCard = $cardState;
    }

    public function shouldDrawTrumpCard(): bool
    {
        // todo check gameSettings?
        return true;
    }
}
