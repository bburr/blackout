<?php declare(strict_types=1);

namespace App\State;

class RoundState extends AbstractState
{
    protected array $bets = [];

    protected array $plays = [];

    protected ?CardState $trumpCard = null;

    public function __construct(protected int $roundNumber, protected int $numCards, protected bool $isNumCardsAscending, protected int $nextPlayerIndexToBet, protected int $nextPlayerIndexToPlay)
    {
    }

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

    public function getPlays(): array
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

    public function jsonSerialize()
    {
        return [
            'round_number' => $this->roundNumber,
            'num_cards' => $this->numCards,
            'trump_card' => $this->trumpCard,
            'is_num_cards_ascending' => $this->isNumCardsAscending,
            'next_player_index_to_bet' => $this->nextPlayerIndexToBet,
            'next_player_index_to_play' => $this->nextPlayerIndexToPlay,
            'bets' => $this->bets,
            'plays' => $this->plays,
        ];
    }

    public static function loadFromSaveData(array $roundData): static
    {
        $round = (new RoundState($roundData['round_number'], $roundData['num_cards'], $roundData['is_num_cards_ascending'], $roundData['next_player_index_to_bet'], $roundData['next_player_index_to_play']));

        if (isset($roundData['trump_card'])) {
            $round->setTrumpCard(new CardState($roundData['trump_card']['suit'], $roundData['trump_card']['value']));
        }

        $round->setBets($roundData['bets']);

        $plays = [];

        foreach ($roundData['plays'] as $index => $card) {
            $plays[$index] = new CardState($card['suit'], $card['value']);
        }

        $round->setPlays($plays);

        return $round;
    }

    public function makeBetForNextPlayer(int $bet): void
    {
        if ($bet < 0 || $bet > $this->numCards) {
            // todo exception
            throw new \LogicException('Invalid bet amount');
        }

        $this->bets[$this->nextPlayerIndexToBet] = $bet;
    }

    public function makePlayForNextPlayer(CardState $cardState): void
    {
        $this->plays[$this->nextPlayerIndexToPlay] = $cardState;
    }

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

    public function setPlays(array $plays): void
    {
        $this->plays = $plays;
    }

    public function setTrumpCard(CardState $cardState): void
    {
        $this->trumpCard = $cardState;
    }
}
