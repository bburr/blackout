<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\MakeBetForNextPlayer;
use App\Models\Game;
use App\Models\User;
use App\State\GameState;
use Illuminate\Database\Eloquent\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

class MakeBetForNextPlayerTest extends TestCase
{
    public function testHandle(): void
    {
        $bet = 1;

        $game = $this->partialMock(Game::class, function (MockInterface $mock) {
            $mock->shouldReceive('getUsers')->andReturn(new Collection(User::factory(3)->make()));
        });

        $gameState = new GameState($game, null);

        $subject = new MakeBetForNextPlayer($gameState, $bet);

        $subject->handle();

        $this->assertCount(1, $gameState->getCurrentRound()->getBets());
    }
}
