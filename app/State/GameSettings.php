<?php declare(strict_types=1);

namespace App\State;

use App\Exceptions\InvalidTrickNumberSettingsException;

/**
 * @phpstan-consistent-constructor
 * @phpstan-type SerializedGameSettings array{ending_num_tricks: int, max_num_tricks: int, points_for_correct_bet: int, starting_num_tricks: int}
 * @phpstan-type InputGameSettings array{ending_num_tricks?: int, max_num_tricks?: int, points_for_correct_bet?: int, starting_num_tricks?: int}
 */
class GameSettings extends AbstractState
{
    protected int $endingNumTricks;
    protected int $maxNumTricks;
    protected int $pointsForCorrectBet;
    protected int $startingNumTricks;

    /**
     * @param int $numPlayers
     * @param array $settings
     * @phpstan-param SerializedGameSettings|InputGameSettings $settings
     * @throws InvalidTrickNumberSettingsException
     */
    public function __construct(int $numPlayers, array $settings = [])
    {
        $this->endingNumTricks = $settings['ending_num_tricks'] ?? 1;
        $maxPossibleTricks = $this->getMaxPossibleTricks($numPlayers);
        $this->maxNumTricks = isset($settings['max_num_tricks']) ? min($settings['max_num_tricks'], $maxPossibleTricks) : $maxPossibleTricks;
        $this->pointsForCorrectBet = $settings['points_for_correct_bet'] ?? 10;
        $this->startingNumTricks = $settings['starting_num_tricks'] ?? 1;

        if ($this->startingNumTricks > $this->maxNumTricks) {
            throw new InvalidTrickNumberSettingsException('starting');
        }

        if ($this->endingNumTricks > $this->maxNumTricks) {
            throw new InvalidTrickNumberSettingsException('ending');
        }
    }

    public function getEndingNumTricks(): int
    {
        return $this->endingNumTricks;
    }

    public function getMaxNumTricks(): int
    {
        return $this->maxNumTricks;
    }

    public static function getMaxPlayers(): int
    {
        return 7;
    }

    public function getMaxPossibleTricks(int $numPlayers): int
    {
        return (int) ((static::getNumDecks() * CardShoeState::NUM_CARDS_PER_DECK) / $numPlayers);
    }

    public static function getMinPlayers(): int
    {
        return 2;
    }

    public static function getNumDecks(): int
    {
        return 1;
    }

    public function getPointsForCorrectBet(): int
    {
        return $this->pointsForCorrectBet;
    }

    public function getStartingNumTricks(): int
    {
        return $this->startingNumTricks;
    }

    /**
     * @phpstan-return SerializedGameSettings
     */
    public function jsonSerialize(): array
    {
        return [
            'ending_num_tricks' => $this->endingNumTricks,
            'max_num_tricks' => $this->maxNumTricks,
            'points_for_correct_bet' => $this->pointsForCorrectBet,
            'starting_num_tricks' => $this->startingNumTricks,
        ];
    }

    /**
     * @param int $numPlayers
     * @param array $gameSettingsData
     * @phpstan-param SerializedGameSettings $gameSettingsData
     * @return static
     * @throws InvalidTrickNumberSettingsException
     */
    public static function loadFromSaveData(int $numPlayers, array $gameSettingsData): static
    {
        return new static($numPlayers, $gameSettingsData);
    }
}
