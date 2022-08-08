<?php declare(strict_types=1);

namespace App\State;

/**
 * @phpstan-consistent-constructor
 * @phpstan-type SerializedRoundScoreState array{round_number: int, scores: int[]}
 */
class RoundScoreState extends AbstractState
{
    /**
     * @param int $roundNumber
     * @param int[] $scores
     */
    public function __construct(protected int $roundNumber, protected array $scores)
    {
    }

    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    /**
     * @return int[]
     */
    public function getScores(): array
    {
        return $this->scores;
    }

    /**
     * @param array $roundScoreStateData
     * @phpstan-param SerializedRoundScoreState $roundScoreStateData
     * @return static
     */
    public static function loadFromSaveData(array $roundScoreStateData): static
    {
        return new static($roundScoreStateData['round_number'], $roundScoreStateData['scores']);
    }

    /**
     * @phpstan-return SerializedRoundScoreState
     */
    public function jsonSerialize(): array
    {
        return [
            'round_number' => $this->roundNumber,
            'scores' => $this->scores,
        ];
    }
}
