<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Exceptions\GameIsCompleteException;
use App\Jobs\NextRound;
use App\Jobs\StartRound;
use App\State\GameSettings;
use App\State\GameState;
use App\State\RoundState;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;

class NextRoundTest extends TestCase
{
    public function dataHandle(): array
    {
        return [
            // round/expected, numTricks/expected, maxNumTricks, endingNumTricks, isNumTricksAscending/expected
            [1, 2, 3, 4, 6, 3, true, true],
            [2, 3, 4, 5, 6, 3, true, true],
            [3, 4, 5, 6, 6, 3, true, false],
            [4, 5, 6, 5, 6, 3, false, false],
            [5, 6, 5, 4, 6, 3, false, false],
            [6, 7, 4, 3, 6, 3, false, false],
            [7, 8, 3, null, 6, 3, false, false],
        ];
    }

    /**
     * @dataProvider dataHandle
     * @param int $roundNumber
     * @param int $expectedRoundNumber
     * @param int $numTricks
     * @param int|null $expectedNumTricks
     * @param int $maxNumTricks
     * @param int $endingNumTricks
     * @param bool $isNumTricksAscending
     * @param bool $expectedIsNumTricksAscending
     * @return void
     */
    public function testHandle(int $roundNumber, int $expectedRoundNumber, int $numTricks, ?int $expectedNumTricks, int $maxNumTricks, int $endingNumTricks, bool $isNumTricksAscending, bool $expectedIsNumTricksAscending): void
    {
        /** @var GameState $gameState */
        $gameState = $this->partialMock(GameState::class, function (MockInterface $mock) use ($isNumTricksAscending, $numTricks, $roundNumber, $maxNumTricks, $endingNumTricks, $expectedNumTricks)  {
            $round = $this->partialMock(RoundState::class, function (MockInterface $mock) use ($isNumTricksAscending, $numTricks, $roundNumber) {
                $mock->shouldReceive('isNumTricksAscending')->andReturn($isNumTricksAscending);
                $mock->shouldReceive('getNumTricks')->andReturn($numTricks);
                $mock->shouldReceive('getRoundNumber')->andReturn($roundNumber);
            });
            $mock->shouldReceive('getCurrentRound')->andReturn($round);

            $gameSettings = $this->partialMock(GameSettings::class, function (MockInterface $mock) use ($maxNumTricks, $endingNumTricks, $isNumTricksAscending) {
                $mock->shouldReceive('getMaxNumTricks')->andReturn($maxNumTricks)->times($isNumTricksAscending ? 1 : 0);
                $mock->shouldReceive('getEndingNumTricks')->andReturn($endingNumTricks)->times($isNumTricksAscending ? 0 : 1);
            });
            $mock->shouldReceive('getGameSettings')->andReturn($gameSettings);

            $mock->shouldReceive('addPreviousRound');
            $mock->shouldReceive('advanceDealerIndex')->times($expectedNumTricks !== null ? 1 : 0);
        });

        $subject = new NextRound($gameState);

        Bus::fake();

        if ($expectedNumTricks === null) {
            $this->expectException(GameIsCompleteException::class);
        }

        $subject->handle();

        if ($expectedNumTricks === null) {
            Bus::assertNotDispatched(StartRound::class);
        }
        else {
            Bus::assertDispatched(function (StartRound $job) use ($expectedRoundNumber, $expectedNumTricks, $expectedIsNumTricksAscending) {
                $this->assertEquals($expectedRoundNumber, $this->getPropertyValueFromObject($job, 'roundNumber'));
                $this->assertEquals($expectedNumTricks, $this->getPropertyValueFromObject($job, 'numTricks'));
                $this->assertEquals($expectedIsNumTricksAscending, $this->getPropertyValueFromObject($job, 'isNumTricksAscending'));

                return true;
            });
        }
    }
}
