<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\State\CardState;
use App\State\TrickState;
use Tests\TestCase;

class TrickStateTest extends TestCase
{
    public function testConstructor(): void
    {
        $trickState = new TrickState();
        $this->assertNull($trickState->getLeadingCard());
        $this->assertCount(0, $trickState->getPlays());
    }

    public function testIsTrickDone(): void
    {
        $trickState = new TrickState();
        $this->assertTrue($trickState->isTrickDone(0));

        $trickState->makePlayForPlayer(0, new CardState('S', 12));
        $trickState->makePlayForPlayer(1, new CardState('D', 12));
        $trickState->makePlayForPlayer(2, new CardState('C', 12));

        $this->assertTrue($trickState->isTrickDone(3));
    }

    public function testLoadFromSaveDataEmpty(): void
    {
        $trickState = TrickState::loadFromSaveData(['leading_card' => null, 'trick_winner_index' => null, 'plays' => []]);
        $this->assertNull($trickState->getLeadingCard());
        $this->assertCount(0, $trickState->getPlays());
        $this->assertNull($trickState->getTrickWinnerIndex());
    }

    public function testLoadFromSaveData(): void
    {
        $trickState = TrickState::loadFromSaveData([
            'leading_card' => ['suit' => 'S', 'value' => 12],
            'trick_winner_index' => 1,
            'plays' => [
                ['suit' => 'S', 'value' => 12],
            ],
        ]);

        $this->assertEquals('A of Spades', (string) $trickState->getLeadingCard());
        $this->assertCount(1, $trickState->getPlays());
        $this->assertEquals(1, $trickState->getTrickWinnerIndex());
    }

    public function testMakePlayForPlayer(): void
    {
        $trickState = new TrickState();
        $this->assertNull($trickState->getLeadingCard());

        $leadingCard = new CardState('S', 12);
        $trickState->makePlayForPlayer(0, $leadingCard);

        $this->assertCount(1, $trickState->getPlays());
        $this->assertEquals((string) $leadingCard, (string) $trickState->getLeadingCard());

        $trickState->makePlayForPlayer(1, new CardState('D', 12));
        $this->assertCount(2, $trickState->getPlays());
        $this->assertEquals((string) $leadingCard, (string) $trickState->getLeadingCard());
    }
}
