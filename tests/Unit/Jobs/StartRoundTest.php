<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\DealForRound;
use App\Jobs\StartRound;
use App\State\GameState;
use Mockery\MockInterface;
use Tests\TestCase;

class StartRoundTest extends TestCase
{
    public function testHandle(): void
    {
        $roundNumber = 1;
        $numTricks = 3;
        $isNumTricksAscending = true;

        $gameState = $this->partialMock(GameState::class, function (MockInterface $mock) {
            $mock->shouldReceive('getDealerIndex')->andReturn(0);
            $mock->shouldReceive('getPlayerIndexAfter')->andReturn(1);
        });

        // todo test setLeadingPlayerIndex
        $subject = new StartRound($gameState, $roundNumber, $numTricks, $isNumTricksAscending);

        $this->expectsJobs(DealForRound::class);

        $subject->handle();
    }
}
