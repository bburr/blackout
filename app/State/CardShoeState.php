<?php declare(strict_types=1);

namespace App\State;

use Illuminate\Support\Collection;

class CardShoeState extends AbstractState
{
    /** @var Collection<CardState> */
    protected Collection $cards;

    public function __construct(?int $numDecks = 1)
    {
        if ($numDecks === null) {
            return;
        }

        $cards = collect();

        for ($i = 0; $i < $numDecks; $i++) {
            foreach (CardState::listSuits() as $suitKey => $suitValue) {
                foreach (CardState::listValues() as $valueKey => $valueValue) {
                    $cards->add(new CardState($suitKey, $valueKey));
                }
            }
        }

        $this->cards = $cards->shuffle();
    }

    public function dealCardOut(): CardState
    {
        return $this->cards->shift();
    }

    public function jsonSerialize()
    {
        return [
            'cards' => $this->cards,
        ];
    }

    public static function loadFromSaveData(array $cardShoeData): static
    {
        return (new CardShoeState(null))
            ->setCardsFromArray($cardShoeData['cards']);
    }

    public function setCardsFromArray(array $cards): self
    {
        $this->cards = collect();

        for ($i = 0; $i < count($cards); $i++) {
            $this->cards[$i] = new CardState($cards[$i]['suit'], $cards[$i]['value']);
        }

        return $this;
    }
}
