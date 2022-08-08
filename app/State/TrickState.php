<?php declare(strict_types=1);

namespace App\State;

use App\State\Collections\CardCollection;

/**
 * @phpstan-consistent-constructor
 * @phpstan-import-type SerializedCardState from CardState
 * @phpstan-import-type SerializedCardCollection from CardCollection
 * @phpstan-type SerializedTrickState array{leading_card: SerializedCardState|null, trick_winner_index: int|null, plays: SerializedCardCollection}
 */
class TrickState extends AbstractState
{
    protected ?CardState $leadingCard = null;

    protected CardCollection $plays;

    protected ?int $trickWinnerIndex = null;

    public function __construct()
    {
        $this->plays = new CardCollection();
    }

    public function getLeadingCard(): ?CardState
    {
        return $this->leadingCard;
    }

    public function getPlays(): CardCollection
    {
        return $this->plays;
    }

    public function getTrickWinnerIndex(): ?int
    {
        return $this->trickWinnerIndex;
    }

    public function isTrickDone(int $numPlayers): bool
    {
        return $this->plays->count() === $numPlayers;
    }

    /**
     * @phpstan-return SerializedTrickState
     */
    public function jsonSerialize(): array
    {
        return [
            'leading_card' => $this->leadingCard?->jsonSerialize(),
            'trick_winner_index' => $this->trickWinnerIndex,
            'plays' => $this->plays->jsonSerialize(),
        ];
    }

    /**
     * @param array $trickData
     * @phpstan-param SerializedTrickState $trickData
     * @return static
     */
    public static function loadFromSaveData(array $trickData): static
    {
        $trick = new static();

        $plays = new CardCollection();

        foreach ($trickData['plays'] as $index => $card) {
            $plays->offsetSet($index, new CardState($card['suit'], $card['value']));
        }

        $trick->setPlays($plays);

        if (isset($trickData['leading_card'])) {
            $trick->setLeadingCard(new CardState($trickData['leading_card']['suit'], $trickData['leading_card']['value']));
        }

        if (isset($trickData['trick_winner_index'])) {
            $trick->setTrickWinnerIndex($trickData['trick_winner_index']);
        }

        return $trick;
    }

    public function makePlayForPlayer(int $playerIndex, CardState $cardState): void
    {
        if ($this->getPlays()->isEmpty()) {
            $this->leadingCard = $cardState;
        }

        $this->plays[$playerIndex] = $cardState;
    }

    public function setLeadingCard(CardState $cardState): void
    {
        $this->leadingCard = $cardState;
    }

    public function setPlays(CardCollection $plays): void
    {
        $this->plays = $plays;
    }

    public function setTrickWinnerIndex(int $trickWinnerIndex): void
    {
        $this->trickWinnerIndex = $trickWinnerIndex;
    }
}
