<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Lobby\AddUserToLobby;
use App\Http\Requests\Lobby\CreateLobby;
use App\Http\Requests\Lobby\JoinLobby;
use App\Models\Lobby;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LobbyController extends Controller
{
    public function addUserToLobby(AddUserToLobby $request)
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

        $lobby->users()->attach($request->get('user_id'));
    }

    public function createLobby(CreateLobby $request): Response
    {
        $lobby = new Lobby();
        $lobby->save();

        $lobby->users()->attach($request->get('auth_user_id'), ['is_owner' => true]);

        Session::put(Lobby::CACHE_KEY_CURRENT_LOBBY_ID, $lobby->getKey());

        return response()->json($lobby);
    }

    public function joinLobby(JoinLobby $request): void
    {
        // look up lobby by invite_code
        /** @var Lobby $lobby */
        $lobby = Lobby::query()
            ->with(['users'])
            ->where('invite_code', '=', $request->get('invite_code'))
            ->orderByDesc('created_at')
            ->take(1)
            ->first();

        abort_if(empty($lobby), Response::HTTP_NOT_FOUND, 'No lobby found for that code');

        // check if user is already in lobby
        $userId = $request->get('auth_user_id');

        abort_if($lobby->getUsers()->contains('uuid', '=', $userId), Response::HTTP_CONFLICT, 'You are already in that lobby');

        // add user to lobby
        $lobby->users()->attach($userId);

        Session::put(Lobby::CACHE_KEY_CURRENT_LOBBY_ID, $lobby->getKey());
    }
}
