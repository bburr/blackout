<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Lobby;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LobbyTest extends TestCase
{
    use RefreshDatabase;

    public function testHasInviteCode(): void
    {
        /** @var Lobby $lobby */
        $lobby = Lobby::factory()->makeOne();
        $lobby->save();

        $inviteCode = $lobby->getInviteCode();
        $intCasted = (int) $inviteCode;

        $this->assertIsString($inviteCode);
        $this->assertGreaterThanOrEqual(0, $intCasted);
        $this->assertLessThanOrEqual(999999, $intCasted);
    }
}
