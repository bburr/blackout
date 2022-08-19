<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\UserJoinedLobby;
use App\Http\Requests\Lobby\AddUserToLobby;
use App\Http\Requests\Lobby\CreateLobby;
use App\Http\Requests\Lobby\JoinLobby;
use App\Models\Lobby;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class LobbyController extends Controller
{
    public function addUserToLobby(AddUserToLobby $request): void
    {
        if ($request->has('invite_code')) {
            $lobby = Lobby::query()
                ->where('invite_code', '=', $request->get('invite_code'))
                ->orderByDesc('created_at')
                ->take(1)
                ->first();
        }
        else {
            $lobby = Lobby::find($request->get('lobby_id'));
        }

        /** @var Lobby $lobby */
        $lobby->users()->attach($request->get('user_id'));
    }

    public function createLobby(CreateLobby $request): Response
    {
        $lobby = new Lobby();
        $lobby->save();

        $lobby->users()->attach($request->get('auth_user_id'), ['is_owner' => true]);

        Session::put(Lobby::CACHE_KEY_CURRENT_LOBBY_ID, $lobby->getKey());

        return Redirect::route('lobby', ['lobby' => $lobby->getKey()]);
    }

    public function joinLobby(JoinLobby $request): Response
    {
        // look up lobby by invite_code
        /** @var Lobby|null $lobby */
        $lobby = Lobby::query()
            ->with(['users'])
            ->where('invite_code', '=', $request->get('invite_code'))
            ->orderByDesc('created_at')
            ->take(1)
            ->first();

        abort_if(empty($lobby), Response::HTTP_NOT_FOUND, 'No lobby found for that code');

        // check if user is already in lobby
        $userId = $request->get('auth_user_id');

        /** @var User|null $user */
        $user = User::find($userId);

        abort_if($user === null, Response::HTTP_BAD_REQUEST, 'You do not have a valid session');
        abort_if($lobby->getUsers()->contains('uuid', '=', $userId), Response::HTTP_CONFLICT, 'You are already in that lobby');

        // add user to lobby
        $lobby->users()->attach($userId);

        Session::put(Lobby::CACHE_KEY_CURRENT_LOBBY_ID, $lobby->getKey());

//        broadcast(new UserJoinedLobby($lobby, $user))->toOthers();
        UserJoinedLobby::dispatch($lobby, $user);

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
