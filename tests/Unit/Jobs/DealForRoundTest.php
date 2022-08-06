<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\DealForRound;
use App\State\CardShoeState;
use App\State\CardState;
use App\State\GameState;
use App\State\PlayerState;
use App\State\RoundState;
use Mockery\MockInterface;
use Tests\TestCase;

class DealForRoundTest extends TestCase
{
    public function testHandle(): void
    {
        $numCards = 4;
        $numPlayers = 5;

        /** @var GameState $gameState */
        $gameState = $this->partialMock(GameState::class, function (MockInterface $mock) use ($numCards, $numPlayers) {
            $cardShoe = $this->partialMock(CardShoeState::class, function (MockInterface $mock) {
                $mock->shouldReceive('dealCardOut')->andReturn(new CardState('S', 12));
            });
            $mock->shouldReceive('getCardShoeState')->andReturn($cardShoe);

            $round = $this->partialMock(RoundState::class, function (MockInterface $mock) use ($numCards) {
                $mock->shouldReceive('getNumCards')->andReturn($numCards);
            });
            $mock->shouldReceive('getCurrentRound')->andReturn($round);

            $players = [];

            for ($i = 0; $i < $numPlayers; $i++) {
                $players[] = $this->partialMock(PlayerState::class, function (MockInterface $mock) use ($numCards) {
                    // ensure hands have right number of cards
                    $mock->shouldReceive('addToHand')->times($numCards);
                });
            }

            $mock->shouldReceive('getPlayersInDealingOrder')->andReturnUsing(function () use ($players) {
                foreach ($players as $player) {
                    yield $player;
                }
            });
        });

        $subject = new DealForRound($gameState);

        $subject->handle();

        // check trump card
        $this->assertInstanceOf(CardState::class, $gameState->getCurrentRound()->getTrumpCard());
    }
}
