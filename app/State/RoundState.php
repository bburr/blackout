<?php declare(strict_types=1);

namespace App\State;

class RoundState extends AbstractState
{
    protected ?CardState $trumpCard = null;

    public function __construct(protected int $roundNumber, protected int $numCards, protected bool $isNumCardsAscending)
    {
    }

    public function getNumCards(): int
    {
        return $this->numCards;
    }

    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    public function getTrumpCard(): ?CardState
    {
        return $this->trumpCard;
    }

    public function isNumCardsAscending(): bool
    {
        return $this->isNumCardsAscending;
    }

    public function jsonSerialize()
    {
        return [
            'round_number' => $this->roundNumber,
            'num_cards' => $this->numCards,
            'trump_card' => $this->trumpCard,
            'is_num_cards_ascending' => $this->isNumCardsAscending,
        ];
    }

    public function setIsNumCardsAscending(bool $value)
    {
        $this->isNumCardsAscending = $value;
    }

    public function setTrumpCard(CardState $cardState)
    {
        $this->trumpCard = $cardState;
    }
}
