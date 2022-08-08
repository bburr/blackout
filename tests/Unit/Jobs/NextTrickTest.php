<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\NextTrick;
use App\Models\Game;
use App\Models\User;
use App\State\CardState;
use App\State\GameState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;

class NextTrickTest extends TestCase
{
    public function testHandle(): void
    {
        $game = $this->partialMock(Game::class, function (MockInterface $mock) {
            $mock->shouldReceive('getUsers')->andReturn(new Collection(User::factory(3)->make()));
        });

        $gameState = new GameState($game, null);

        $subject = new NextTrick($gameState);

        $gameState->getCurrentRound()->getCurrentTrick()->setLeadingCard(new CardState('S', 12));

        $nextLeaderIndex = 2;
        Bus::shouldReceive('dispatch')->andReturn($nextLeaderIndex);

        $subject->handle();

        $this->assertEquals($nextLeaderIndex, $gameState->getCurrentRound()->getPreviousTricks()[0]?->getTrickWinnerIndex());
        $this->assertCount(1, $gameState->getCurrentRound()->getPreviousTricks());
        $this->assertCount(0, $gameState->getCurrentRound()->getCurrentTrick()->getPlays());
        $this->assertEquals($nextLeaderIndex, $gameState->getCurrentRound()->getNextPlayerIndexToPlay());
    }
}
