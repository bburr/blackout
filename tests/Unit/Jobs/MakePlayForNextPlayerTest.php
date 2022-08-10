<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Exceptions\InvalidCardForPlayException;
use App\Jobs\MakePlayForNextPlayer;
use App\Jobs\NextTrick;
use App\Models\Game;
use App\Models\User;
use App\State\CardState;
use App\State\Collections\CardCollection;
use App\State\GameState;
use App\State\PlayerState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @phpstan-import-type SerializedCardCollection from CardCollection
 * @phpstan-import-type SerializedCardState from CardState
 */
class MakePlayForNextPlayerTest extends TestCase
{
    /**
     * @phpstan-return array<string, array{0: SerializedCardCollection, 1: SerializedCardState, 2: SerializedCardState|null, 3: bool}>
     */
    public function dataHandle(): array
    {
        return [
            'No leading card' => [
                [
                    [
                        'suit' => 'S',
                        'value' => 12,
                    ],
                ],
                [
                    'suit' => 'S',
                    'value' => 12,
                ],
                null,
                false,
            ],
            'Card not in hand' => [
                [
                    [
                        'suit' => 'S',
                        'value' => 12,
                    ],
                    [
                        'suit' => 'C',
                        'value' => 12,
                    ],
                ],
                [
                    'suit' => 'D',
                    'value' => 12,
                ],
                null,
                true,
            ],
            'No suit match in hand' => [
                [
                    [
                        'suit' => 'S',
                        'value' => 12,
                    ],
                    [
                        'suit' => 'C',
                        'value' => 12,
                    ],
                ],
                [
                    'suit' => 'S',
                    'value' => 12,
                ],
                [
                    'suit' => 'D',
                    'value' => 12,
                ],
                false,
            ],
            'Suit match in hand, valid play' => [
                [
                    [
                        'suit' => 'S',
                        'value' => 12,
                    ],
                    [
                        'suit' => 'D',
                        'value' => 12,
                    ],
                ],
                [
                    'suit' => 'S',
                    'value' => 12,
                ],
                [
                    'suit' => 'S',
                    'value' => 10,
                ],
                false,
            ],
            'Suit match in hand, invalid play' => [
                [
                    [
                        'suit' => 'S',
                        'value' => 12,
                    ],
                    [
                        'suit' => 'D',
                        'value' => 10,
                    ],
                    [
                        'suit' => 'C',
                        'value' => 12,
                    ],
                ],
                [
                    'suit' => 'S',
                    'value' => 12,
                ],
                [
                    'suit' => 'D',
                    'value' => 12,
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataHandle
     * @param array $playerHand
     * @phpstan-param SerializedCardCollection $playerHand
     * @param array $cardData
     * @phpstan-param SerializedCardState $cardData
     * @param array|null $leadingCardData
     * @phpstan-param SerializedCardState|null $leadingCardData
     * @param bool $expectException
     * @return void
     */
    public function testHandle(array $playerHand, array $cardData, ?array $leadingCardData, bool $expectException): void
    {
        $game = $this->partialMock(Game::class, function (MockInterface $mock) {
            $mock->shouldReceive('getUsers')->andReturn(new Collection(User::factory(3)->make()));
        });

        $gameState = new GameState($game);

        if ($leadingCardData !== null) {
            $leadingCard = new CardState($leadingCardData['suit'], $leadingCardData['value']);
            $gameState->getCurrentRound()->getCurrentTrick()->setLeadingCard($leadingCard);
            $firstPlayerIndex = $gameState->getPlayerIndexAfter($gameState->getDealerIndex());
            $gameState->getCurrentRound()->getCurrentTrick()->setPlays(new CardCollection([$firstPlayerIndex => $leadingCard]));
            $gameState->getCurrentRound()->setNextPlayerIndexToPlay($gameState->getPlayerIndexAfter($firstPlayerIndex));
        }

        $playerState = new PlayerState(User::factory()->makeOne());

        $playerState->setHandFromArray($playerHand);

        $cardState = new CardState($cardData['suit'], $cardData['value']);

        $subject = new MakePlayForNextPlayer($gameState, $playerState, $cardState);

        if ($expectException) {
            $this->expectException(InvalidCardForPlayException::class);
        }

        $subject->handle();

        $this->assertCount(count($playerHand) - 1, $playerState->getHand());

        $this->assertCount(isset($leadingCard) && ! $expectException ? 2 : 1, $gameState->getCurrentRound()->getCurrentTrick()->getPlays());
        $this->assertNotNull($gameState->getCurrentRound()->getCurrentTrick()->getLeadingCard());

        if (isset($leadingCard)) {
            $this->assertEquals($leadingCard, $gameState->getCurrentRound()->getCurrentTrick()->getLeadingCard());
        }
        else {
            $this->assertEquals($cardState, $gameState->getCurrentRound()->getCurrentTrick()->getLeadingCard());
        }

        $this->doesntExpectJobs(NextTrick::class);
    }

    public function testHandleNextTrick(): void
    {
        $game = $this->partialMock(Game::class, function (MockInterface $mock) {
            $mock->shouldReceive('getUsers')->andReturn(new Collection(User::factory(3)->make()));
        });

        $gameState = new GameState($game);

        Bus::fake();

        $card = ['suit' => 'S', 'value' => 12];
        $this->makePlay($gameState, [$card], $card);
        Bus::assertNotDispatched(NextTrick::class);

        $this->makePlay($gameState, [$card], $card);
        Bus::assertNotDispatched(NextTrick::class);

        $this->makePlay($gameState, [$card], $card);
        Bus::assertDispatched(NextTrick::class);
    }

    /**
     * @param GameState $gameState
     * @param array $playerHand
     * @phpstan-param SerializedCardCollection $playerHand
     * @param array $cardData
     * @phpstan-param SerializedCardState $cardData
     * @return void
     * @throws InvalidCardForPlayException
     */
    protected function makePlay(GameState $gameState, array $playerHand, array $cardData): void
    {
        $playerState = new PlayerState(User::factory()->makeOne());

        $playerState->setHandFromArray($playerHand);

        $cardState = new CardState($cardData['suit'], $cardData['value']);

        $subject = new MakePlayForNextPlayer($gameState, $playerState, $cardState);

        $subject->handle();
    }
}
