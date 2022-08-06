<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\Models\User;
use App\State\CardState;
use App\State\Collections\CardCollection;
use App\State\PlayerState;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlayerStateTest extends TestCase
{
    public function testConstructor(): void
    {
        $player = new PlayerState($this->partialMock(User::class));
        $this->assertInstanceOf(PlayerState::class, $player);
        $this->assertInstanceOf(CardCollection::class, $player->getHand());
        $this->assertCount(0, $player->getHand());
    }

    public function testAddToHand(): void
    {
        $player = new PlayerState($this->partialMock(User::class));
        $this->assertCount(0, $player->getHand());
        $card = new CardState('S', 12);
        $player->addToHand($card);
        $this->assertCount(1, $player->getHand());
        $this->assertEquals((string) $card, (string) $player->getHand()[0]);
    }

    public function testLoadFromSaveDataEmptyHand(): void
    {
        $player = PlayerState::loadFromSaveData(['user_id' => (string) Str::uuid(), 'hand' => []]);
        $this->assertInstanceOf(PlayerState::class, $player);
        $this->assertInstanceOf(CardCollection::class, $player->getHand());
        $this->assertCount(0, $player->getHand());
    }

    public function testLoadFromSaveData(): void
    {
        $player = PlayerState::loadFromSaveData(['user_id' => (string) Str::uuid(), 'hand' => [
            ['suit' => 'S', 'value' => 12],
            ['suit' => 'C', 'value' => 0],
            ['suit' => 'H', 'value' => 10],
        ]]);
        $this->assertInstanceOf(PlayerState::class, $player);
        $this->assertInstanceOf(CardCollection::class, $player->getHand());
        $this->assertCount(3, $player->getHand());

        $expectCards = [
            'A of Spades',
            '2 of Clubs',
            'Q of Hearts',
        ];

        $this->assertEqualsCanonicalizing($expectCards, $player->getHand()->map(fn ($card) => (string) $card)->toArray());
    }
}
