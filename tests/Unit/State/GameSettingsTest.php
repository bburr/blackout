<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\Exceptions\InvalidTrickNumberSettingsException;
use App\State\GameSettings;
use Tests\TestCase;

class GameSettingsTest extends TestCase
{
    public function testConstructor(): void
    {
        $gameSettings = new GameSettings(3);

        $this->assertEquals(1, $gameSettings->getEndingNumTricks());
        $this->assertEquals(17, $gameSettings->getMaxNumTricks());
        $this->assertEquals(10, $gameSettings->getPointsForCorrectBet());
        $this->assertEquals(1, $gameSettings->getStartingNumTricks());
    }

    public function testLoadFromSaveData(): void
    {
        $endingNumTricks = 1;
        $maxNumTricks = 1;
        $pointsForCorrectBet = 1;
        $startingNumTricks = 1;

        $gameSettings = GameSettings::loadFromSaveData(3, [
            'ending_num_tricks' => $endingNumTricks,
            'max_num_tricks' => $maxNumTricks,
            'points_for_correct_bet' => $pointsForCorrectBet,
            'starting_num_tricks' => $startingNumTricks,
        ]);

        $this->assertEquals($endingNumTricks, $gameSettings->getEndingNumTricks());
        $this->assertEquals($maxNumTricks, $gameSettings->getMaxNumTricks());
        $this->assertEquals($pointsForCorrectBet, $gameSettings->getPointsForCorrectBet());
        $this->assertEquals($startingNumTricks, $gameSettings->getStartingNumTricks());
    }

    public function dataMaxPossibleTricksLogic(): array
    {
        return [
            [2, 26],
            [3, 17],
            [4, 13],
            [5, 10],
            [6, 8],
            [7, 7],
        ];
    }

    /**
     * @dataProvider dataMaxPossibleTricksLogic
     * @param int $numPlayers
     * @param int $expectedValue
     * @return void
     * @throws InvalidTrickNumberSettingsException
     */
    public function testMaxPossibleTricksLogic(int $numPlayers, int $expectedValue): void
    {
        $gameSettings = new GameSettings(3);

        $this->assertEquals($expectedValue, $gameSettings->getMaxPossibleTricks($numPlayers));
    }
}
