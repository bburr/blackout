<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\State\CardState;
use Tests\TestCase;

class CardStateTest extends TestCase
{
    public function testConstructor(): void
    {
        $card = new CardState('S', 12);

        $this->assertEquals('A of Spades', (string) $card);

        $this->assertEquals(['suit' => 'S', 'value' => 12], $card->jsonSerialize());
    }

    public function testData(): void
    {
        $this->assertCount(4, CardState::listSuits());
        $this->assertCount(13, CardState::listValues());
    }
}
