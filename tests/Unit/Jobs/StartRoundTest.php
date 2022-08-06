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
        $numCards = 3;
        $isNumCardsAscending = true;

        $gameState = $this->partialMock(GameState::class, function (MockInterface $mock) {
            $mock->shouldReceive('getDealerIndex')->andReturn(0);
            $mock->shouldReceive('getPlayerIndexAfter')->andReturn(1);
        });

        $subject = new StartRound($gameState, $roundNumber, $numCards, $isNumCardsAscending);

        $this->expectsJobs(DealForRound::class);

        $subject->handle();
    }
}
