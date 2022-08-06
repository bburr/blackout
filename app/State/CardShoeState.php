<?php declare(strict_types=1);

namespace App\State;

use App\Exceptions\CardShoeIsEmptyException;
use App\State\Collections\CardCollection;

/**
 * @phpstan-consistent-constructor
 * @phpstan-import-type SerializedCardState from CardState
 * @phpstan-import-type SerializedCardCollection from CardCollection
 * @phpstan-type SerializedCardShoeState array{cards: SerializedCardCollection}
 */
class CardShoeState extends AbstractState
{
    /** @var CardCollection<int, CardState> */
    protected CardCollection $cards;

    public function __construct(?int $numDecks = 1)
    {
        if ($numDecks === null) {
            return;
        }

        $cards = new CardCollection();

        for ($i = 0; $i < $numDecks; $i++) {
            foreach (CardState::listSuits() as $suitKey => $suitValue) {
                foreach (CardState::listValues() as $valueKey => $valueValue) {
                    $cards->add(new CardState($suitKey, $valueKey));
                }
            }
        }

        $this->cards = $cards->shuffle();
    }

    /**
     * @throws CardShoeIsEmptyException
     */
    public function dealCardOut(): CardState
    {
        /** @var CardState|null $card */
        $card = $this->cards->shift();

        if ($card === null) {
            throw new CardShoeIsEmptyException();
        }

        return $card;
    }

    /**
     * @phpstan-return SerializedCardShoeState
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'cards' => $this->cards->jsonSerialize(),
        ];
    }

    /**
     * @param array $cardShoeData
     * @phpstan-param SerializedCardShoeState $cardShoeData
     * @return static
     */
    public static function loadFromSaveData(array $cardShoeData): static
    {
        return (new static(null))
            ->setCardsFromArray($cardShoeData['cards']);
    }

    /**
     * @param array $cards
     * @phpstan-param SerializedCardCollection $cards
     * @return $this
     */
    public function setCardsFromArray(array $cards): self
    {
        $this->cards = new CardCollection();

        for ($i = 0; $i < count($cards); $i++) {
            $this->cards[$i] = new CardState($cards[$i]['suit'], $cards[$i]['value']);
        }

        return $this;
    }
}
