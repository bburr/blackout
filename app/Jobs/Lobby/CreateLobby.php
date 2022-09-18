<?php declare(strict_types=1);

namespace App\Jobs\Lobby;

use App\Models\Lobby;
use Illuminate\Support\Facades\Bus;

class CreateLobby
{
    public function __construct(protected string $ownerId, protected bool $setSession)
    {
    }

    public function handle(): Lobby
    {
        $lobby = new Lobby();
        $lobby->save();

        $lobby->users()->attach($this->ownerId, ['is_owner' => true]);

        if ($this->setSession) {
            Bus::dispatch(new SetSessionCurrentLobby($lobby));
        }

        return $lobby;
    }
}
