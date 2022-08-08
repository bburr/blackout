<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\State\RoundScoreState;
use Tests\TestCase;

class RoundScoreStateTest extends TestCase
{
    public function testConstructor(): void
    {
        $roundNumber = 1;
        $scores = [10, 0, 0];

        $roundScoreState = new RoundScoreState($roundNumber, $scores);

        $this->assertEquals($roundNumber, $roundScoreState->getRoundNumber());
        $this->assertEquals($scores, $roundScoreState->getScores());
    }

    public function testLoadFromSaveData(): void
    {
        $roundNumber = 1;
        $scores = [10, 0, 0];

        $roundScoreStateData = [
            'round_number' => $roundNumber,
            'scores' => $scores,
        ];

        $roundScoreState = RoundScoreState::loadFromSaveData($roundScoreStateData);

        $this->assertEquals($roundNumber, $roundScoreState->getRoundNumber());
        $this->assertEquals($scores, $roundScoreState->getScores());
    }
}
