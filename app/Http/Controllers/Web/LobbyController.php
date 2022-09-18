<?php declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Events\UserJoinedLobby;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lobby\AddUserToLobby;
use App\Http\Requests\Lobby\CreateLobbyRequest;
use App\Http\Requests\Lobby\JoinLobbyRequest;
use App\Jobs\Lobby\CreateLobby;
use App\Jobs\Lobby\JoinLobby;
use App\Models\Lobby;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class LobbyController extends Controller
{
    public function createLobby(CreateLobbyRequest $request): Response
    {
        /** @var Lobby $lobby */
        $lobby = Bus::dispatch(new CreateLobby($request->getAuthUserId(), setSession: true));

        return Redirect::route('lobby', ['lobby' => $lobby->getKey()]);
    }

    public function joinLobby(JoinLobbyRequest $request): Response
    {
        $lobby = Bus::dispatch(new JoinLobby($request->getAuthUserId(), $request->getInviteCode(), setSession: true));

        return Redirect::route('lobby', ['lobby' => $lobby->getKey()]);
    }

    public function lobby(Lobby $lobby, Request $request): InertiaResponse
    {
        $userId = $request->get('auth_user_id');
        $lobbyUsers = $lobby->getUsers();

        abort_unless($lobbyUsers->contains('uuid', '=', $userId), Response::HTTP_UNAUTHORIZED, 'You are not in that lobby');

        $users = $lobbyUsers->map(fn (User $user) => [
            'id' => $user->getKey(),
            'name' => $user->getName(),
        ])->toArray();

        /** @var User $lobbyOwner */
        $lobbyOwner = $lobbyUsers->firstWhere('pivot.is_owner', '=', true);
        $isOwner = $lobbyOwner->getKey() === $userId;

        return Inertia::render('Lobby', [
            'lobbyId' => $lobby->getKey(),
            'users' => $users,
            'isOwner' => $isOwner,
            'inviteCode' => $isOwner ? $lobby->getInviteCode() : null,
        ]);
    }
}
