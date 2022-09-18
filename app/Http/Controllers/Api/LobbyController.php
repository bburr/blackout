<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\UserJoinedLobby;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lobby\AddUserToLobby;
use App\Http\Requests\Lobby\CreateLobbyRequest;
use App\Jobs\Lobby\CreateLobby;
use App\Models\Lobby;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;

class LobbyController extends Controller
{
    public function addUserToLobby(AddUserToLobby $request): void
    {
        if ($request->has('invite_code')) {
            $lobby = Lobby::query()
                ->where('invite_code', '=', $request->getInviteCode())
                ->orderByDesc('created_at')
                ->take(1)
                ->first();
        }
        else {
            $lobby = Lobby::find($request->getLobbyId());
        }

        abort_if($lobby === null, Response::HTTP_NOT_FOUND, 'No lobby found');

        $user = User::find($request->getUserId());

        abort_if($user === null, Response::HTTP_NOT_FOUND, 'No user found');

        /** @var Lobby $lobby */
        $lobby->users()->attach($user);

        broadcast(new UserJoinedLobby($lobby, $user))->toOthers();
    }

    public function createLobby(CreateLobbyRequest $request): Response
    {
        /** @var Lobby $lobby */
        $lobby = Bus::dispatch(new CreateLobby($request->getAuthUserId(), setSession: true));

        return response()->json($lobby);
    }
}
