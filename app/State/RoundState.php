<?php declare(strict_types=1);

namespace App\State;

use App\Exceptions\InvalidBetAmountException;
use App\State\Collections\CardCollection;

/**
 * @phpstan-consistent-constructor
 * @phpstan-import-type SerializedCardState from CardState
 * @phpstan-import-type SerializedCardCollection from CardCollection
 * @phpstan-type SerializedRoundConfig array{round_number: int, num_cards: int, is_num_cards_ascending: bool, next_player_index_to_bet: int, next_player_index_to_play: int}
 * @phpstan-type SerializedRoundState array{config: SerializedRoundConfig, trump_card: SerializedCardState|null, bets: int[], plays: SerializedCardCollection}
 */
class RoundState extends AbstractState
{
    /**
     * @var array<int, int>
     */
    protected array $bets = [];

    protected CardCollection $plays;

    protected ?CardState $trumpCard = null;

    public function __construct(protected int $roundNumber, protected int $numCards, protected bool $isNumCardsAscending, protected int $nextPlayerIndexToBet, protected int $nextPlayerIndexToPlay)
    {
        $this->plays = new CardCollection();
    }

    /**
     * @return int[]
     */
    public function getBets(): array
    {
        return $this->bets;
    }

    public function getNextPlayerIndexToBet(): int
    {
        return $this->nextPlayerIndexToBet;
    }

    public function getNextPlayerIndexToPlay(): int
    {
        return $this->nextPlayerIndexToPlay;
    }

    public function getNumCards(): int
    {
        return $this->numCards;
    }

    public function getPlays(): CardCollection
    {
        return $this->plays;
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

    public function isNumCardsAscending(): bool
    {
        return $this->isNumCardsAscending;
    }

    public function isPlayDone(): bool
    {
        return $this->nextPlayerIndexToPlay < 0;
    }

    /**
     * @phpstan-return SerializedRoundState
     */
    public function jsonSerialize(): array
    {
        return [
            'config' => [
                'round_number' => $this->roundNumber,
                'num_cards' => $this->numCards,
                'is_num_cards_ascending' => $this->isNumCardsAscending,
                'next_player_index_to_bet' => $this->nextPlayerIndexToBet,
                'next_player_index_to_play' => $this->nextPlayerIndexToPlay,
            ],
            'trump_card' => $this->trumpCard?->jsonSerialize(),
            'bets' => $this->bets,
            'plays' => $this->plays->jsonSerialize(),
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
            $roundData['config']['num_cards'],
            $roundData['config']['is_num_cards_ascending'],
            $roundData['config']['next_player_index_to_bet'],
            $roundData['config']['next_player_index_to_play']
        ));

        if (isset($roundData['trump_card'])) {
            $round->setTrumpCard(new CardState($roundData['trump_card']['suit'], $roundData['trump_card']['value']));
        }

        $round->setBets($roundData['bets']);

        $plays = new CardCollection();

        foreach ($roundData['plays'] as $index => $card) {
            $plays->offsetSet($index, new CardState($card['suit'], $card['value']));
        }

        $round->setPlays($plays);

        return $round;
    }

    /**
     * @throws InvalidBetAmountException
     */
    public function makeBetForNextPlayer(int $bet): void
    {
        if ($bet < 0 || $bet > $this->numCards) {
            throw new InvalidBetAmountException();
        }

        $this->bets[$this->nextPlayerIndexToBet] = $bet;
    }

    public function makePlayForNextPlayer(CardState $cardState): void
    {
        $this->plays[$this->nextPlayerIndexToPlay] = $cardState;
    }

    /**
     * @param int[] $bets
     * @return void
     */
    public function setBets(array $bets): void
    {
        $this->bets = $bets;
    }

    public function setIsNumCardsAscending(bool $value): void
    {
        $this->isNumCardsAscending = $value;
    }

    public function setNextPlayerIndexToBet(int $index): void
    {
        $this->nextPlayerIndexToBet = $index;
    }

    public function setNextPlayerIndexToPlay(int $index): void
    {
        $this->nextPlayerIndexToPlay = $index;
    }

    public function setPlays(CardCollection $plays): void
    {
        $this->plays = $plays;
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
