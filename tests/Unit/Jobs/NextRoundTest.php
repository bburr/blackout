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
            // round/expected, numCards/expected, maxNumCards, endingNumCards, isNumCardsAscending/expected
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
     * @param int $numCards
     * @param int|null $expectedNumCards
     * @param int $maxNumCards
     * @param int $endingNumCards
     * @param bool $isNumCardsAscending
     * @param bool $expectedIsNumCardsAscending
     * @return void
     */
    public function testHandle(int $roundNumber, int $expectedRoundNumber, int $numCards, ?int $expectedNumCards, int $maxNumCards, int $endingNumCards, bool $isNumCardsAscending, bool $expectedIsNumCardsAscending): void
    {
        /** @var GameState $gameState */
        $gameState = $this->partialMock(GameState::class, function (MockInterface $mock) use ($isNumCardsAscending, $numCards, $roundNumber, $maxNumCards, $endingNumCards, $expectedNumCards)  {
            $round = $this->partialMock(RoundState::class, function (MockInterface $mock) use ($isNumCardsAscending, $numCards, $roundNumber) {
                $mock->shouldReceive('isNumCardsAscending')->andReturn($isNumCardsAscending);
                $mock->shouldReceive('getNumCards')->andReturn($numCards);
                $mock->shouldReceive('getRoundNumber')->andReturn($roundNumber);
            });
            $mock->shouldReceive('getCurrentRound')->andReturn($round);

            $gameSettings = $this->partialMock(GameSettings::class, function (MockInterface $mock) use ($maxNumCards, $endingNumCards, $isNumCardsAscending) {
                $mock->shouldReceive('getMaxNumCards')->andReturn($maxNumCards)->times($isNumCardsAscending ? 1 : 0);
                $mock->shouldReceive('getEndingNumCards')->andReturn($endingNumCards)->times($isNumCardsAscending ? 0 : 1);
            });
            $mock->shouldReceive('getGameSettings')->andReturn($gameSettings);

            $mock->shouldReceive('addPreviousRound');
            $mock->shouldReceive('advanceDealerIndex')->times($expectedNumCards !== null ? 1 : 0);
        });

        $subject = new NextRound($gameState);

        Bus::fake();

        if ($expectedNumCards === null) {
            $this->expectException(GameIsCompleteException::class);
        }

        $subject->handle();

        if ($expectedNumCards === null) {
            Bus::assertNotDispatched(StartRound::class);
        }
        else {
            Bus::assertDispatched(function (StartRound $job) use ($expectedRoundNumber, $expectedNumCards, $expectedIsNumCardsAscending) {
                $this->assertEquals($expectedRoundNumber, $this->getPropertyValueFromObject($job, 'roundNumber'));
                $this->assertEquals($expectedNumCards, $this->getPropertyValueFromObject($job, 'numCards'));
                $this->assertEquals($expectedIsNumCardsAscending, $this->getPropertyValueFromObject($job, 'isNumCardsAscending'));

                return true;
            });
        }
    }
}
