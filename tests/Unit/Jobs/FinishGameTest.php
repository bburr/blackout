<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\FinishGame;
use App\Models\Game;
use App\Models\User;
use App\State\GameState;
use App\State\RoundScoreState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mockery\MockInterface;
use Tests\TestCase;

class FinishGameTest extends TestCase
{
    public function dataHandle(): array
    {
        return [
            [
                [
                    [10, 0, 0],
                ],
                0,
            ],
            [
                [
                    [10, 0, 0],
                    [10, 11, 0],
                    [0, 10, 0],
                ],
                1,
            ],
        ];
    }

    /**
     * @dataProvider dataHandle
     * @param array $scoresData
     * @param int $winnerIndex
     * @return void
     */
    public function testHandle(array $scoresData, int $winnerIndex): void
    {
        // todo test tiebreaker logic
        $game = $this->partialMock(Game::class, function (MockInterface $mock) use ($scoresData, $winnerIndex) {
            $users = new Collection(User::factory(count($scoresData[0]))->make());
            $mock->shouldReceive('getUsers')->andReturn($users);

            $winnerUser = $users->get($winnerIndex);
            $relation = $this->partialMock(BelongsTo::class, function (MockInterface $mock) use ($winnerUser) {
                $mock->shouldReceive('associate')->with($winnerUser);
            });

            $mock->shouldReceive('winner')->andReturn($relation);
            $mock->shouldReceive('save');
        });

        $gameState = new GameState($game, null);

        foreach ($scoresData as $i => $scoreData) {
            $gameState->addPreviousRoundScore(new RoundScoreState($i + 1, $scoreData));
        }

        $subject = new FinishGame($gameState);

        $subject->handle();
    }
}
