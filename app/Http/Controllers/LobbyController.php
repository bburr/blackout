<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLobby;
use App\Models\Lobby;
use Illuminate\Support\Facades\Session;

class LobbyController extends Controller
{
    public function createLobby(CreateLobby $request)
    {
        $lobby = (new Lobby())
            ->fill([
                'owner' => $request->get('auth_user_id'),
            ]);
        $lobby->save();

        Session::put('active-owned-lobby-id', $lobby->getKey());

        return response()->json($lobby);
    }

    public function getInviteCode()
    {

    }

    public function joinLobby()
    {

    }
}
