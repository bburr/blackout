<?php declare(strict_types=1);

namespace App\Jobs\Lobby;

use App\Events\UserJoinedLobby;
use App\Models\Lobby;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class JoinLobby
{
    public function __construct(protected string $userId, protected string $inviteCode, protected bool $setSession)
    {
    }

    public function handle(): Lobby
    {
        // look up lobby by invite_code
        /** @var Lobby|null $lobby */
        $lobby = Lobby::query()
            ->with(['users'])
            ->where('invite_code', '=', $this->inviteCode)
            ->orderByDesc('created_at')
            ->take(1)
            ->first();

        abort_if($lobby === null, Response::HTTP_NOT_FOUND, 'No lobby found for that code');

        // check if user is already in lobby
        /** @var User|null $user */
        $user = User::find($this->userId);

        abort_if($user === null, Response::HTTP_BAD_REQUEST, 'You do not have a valid session');
        abort_if($lobby->getUsers()->contains('uuid', '=', $this->userId), Response::HTTP_CONFLICT, 'You are already in that lobby');

        // add user to lobby
        $lobby->users()->attach($this->userId);

        if ($this->setSession) {
            Bus::dispatch(new SetSessionCurrentLobby($lobby));
        }

        broadcast(new UserJoinedLobby($lobby, $user))->toOthers();

        return $lobby;
    }
}
