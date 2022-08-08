<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\Models\Game;
use App\Models\User;
use App\State\CardShoeState;
use App\State\GameState;
use App\State\Handlers\GameStateCacheHandlerInterface;
use App\State\PlayerState;
use App\State\RoundState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class GameStateTest extends TestCase
{
    public function testConstructorInit(): void
    {
        $numUsers = 3;
        $gameState = $this->getBasicGameState($numUsers);

        $this->assertGreaterThanOrEqual(0, $gameState->getDealerIndex());
        $this->assertLessThanOrEqual($numUsers - 1, $gameState->getDealerIndex());
        $this->assertLessThanOrEqual($numUsers - 1, $gameState->getLeadingPlayerIndex());
        $this->assertTrue($gameState->getDealerIndex() !== $gameState->getLeadingPlayerIndex());
        $this->assertInstanceOf(RoundState::class, $gameState->getCurrentRound());
        $this->assertEquals($numUsers, $gameState->getPlayers()->count());
        $this->assertInstanceOf(CardShoeState::class, $gameState->getCardShoeState());
    }

    public function testConstructorLoad(): void
    {
        $gameKey = (string) Str::uuid();

        $this->bindCacheHandler($gameKey, [
            GameState::CARD_SHOE_CACHE_KEY . $gameKey => [
                'cards' => [],
            ],
            GameState::CURRENT_ROUND_CACHE_KEY . $gameKey => [
                'config' => [
                    'round_number' => 1,
                    'num_tricks' => 3,
                    'is_num_tricks_ascending' => true,
                    'next_player_index_to_bet' => 0,
                    'next_player_index_to_play' => 0,
                ],
                'bets' => [],
                'current_trick' => ['plays' => []],
                'previous_tricks' => [],
            ],
            GameState::GAME_STATE_CACHE_KEY . $gameKey => [
                'dealer_index' => 0,
                'leading_player_index' => 0,
            ],
            GameState::PLAYERS_CACHE_KEY . $gameKey => [
                (new PlayerState(User::factory()->makeOne()))->jsonSerialize(),
                (new PlayerState(User::factory()->makeOne()))->jsonSerialize(),
                (new PlayerState(User::factory()->makeOne()))->jsonSerialize(),
            ],
            GameState::PREVIOUS_ROUNDS_CACHE_KEY . $gameKey => [],
        ]);

        $game = $this->partialMock(Game::class, function (MockInterface $mock) use ($gameKey) {
            $mock->shouldReceive('getKey')->andReturn($gameKey);
        });

        $gameState = new GameState($game, null);

        // todo
        $this->markTestIncomplete('waiting to add assertions until more loading logic is implemented');
    }

    public function testAdvancePlayerIndexUntilLeadingPlayer(): void
    {
        $numUsers = 3;
        $gameState = $this->getBasicGameState($numUsers);
        $this->assertNotEquals(-1, $gameState->getCurrentRound()->getNextPlayerIndexToPlay());
        $playerIndex = $gameState->getLeadingPlayerIndex();

        for ($i = 0; $i < $numUsers - 1; $i++) {
            $playerIndex = $gameState->advancePlayerIndexUntilLeadingPlayer($playerIndex);
            $this->assertNotEquals(-1, $playerIndex);
        }

        $playerIndex = $gameState->advancePlayerIndexUntilLeadingPlayer($playerIndex);
        $this->assertEquals(-1, $playerIndex);
    }

    public function testAdvanceDealerIndex(): void
    {
        $numUsers = 3;
        $gameState = $this->getBasicGameState($numUsers);

        for ($i = 0; $i < $numUsers * 10; $i++) {
            $gameState->advanceDealerIndex();
            $this->assertNotEquals(-1, $gameState->getDealerIndex());
        }
    }

    public function testGetPlayerIndexAfter(): void
    {
        $gameState = $this->getBasicGameState(5);

        $this->assertEquals(1, $gameState->getPlayerIndexAfter(0));
        $this->assertEquals(2, $gameState->getPlayerIndexAfter(1));
        $this->assertEquals(3, $gameState->getPlayerIndexAfter(2));
        $this->assertEquals(4, $gameState->getPlayerIndexAfter(3));
        $this->assertEquals(0, $gameState->getPlayerIndexAfter(4));
    }

    protected function bindCacheHandler(string $gameKey, array $data): void
    {
        $this->app->bind(GameStateCacheHandlerInterface::class, fn () => new class($gameKey, $data) implements GameStateCacheHandlerInterface {
            public function __construct(protected string $gameKey, protected array $data)
            {
            }

            public function cacheGet(string $key): mixed
            {
                return $this->data[$key . $this->gameKey] ?? null;
            }

            public function cacheHas(string $key): bool
            {
                return isset($this->data[$key . $this->gameKey]);
            }

            public function cachePut(string $key, mixed $value): void
            {
            }
        });
    }

    protected function getBasicGameState(int $numUsers): GameState
    {
        $this->bindCacheHandler('', []);

        $game = $this->partialMock(Game::class, function (MockInterface $mock) use ($numUsers) {
            $mock->shouldReceive('getKey')->andReturn((string) Str::uuid());

            $mock->shouldReceive('getUsers')->andReturn(new Collection(User::factory($numUsers)->make()));
        });

        return new GameState($game, null);
    }
}
