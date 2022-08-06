<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\State\GameState;
use App\State\RoundState;
use Mockery\MockInterface;
use Tests\TestCase;

class MakeBetForNextPlayerTest extends TestCase
{
    public function testHandle(): void
    {
        $bet = 1;

        $this->partialMock(GameState::class, function (MockInterface $mock) use ($bet) {
            $round = $this->partialMock(RoundState::class, function (MockInterface $mock) use ($bet) {
                $mock->shouldReceive('makeBetForNextPlayer')->with($bet);
            });

            $mock->shouldReceive('getCurrentRound')->andReturn($round);
            $mock->shouldReceive('advanceBettingPlayerIndex');
        });
    }
}
