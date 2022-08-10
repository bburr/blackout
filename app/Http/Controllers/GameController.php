<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InvalidGameSettingsException;
use App\Http\Requests\Game\StartGame;
use App\Models\Game;
use App\Models\Lobby;
use App\Models\User;
use App\State\GameSettings;
use App\State\GameState;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function startGame(StartGame $request): Response
    {
        // check for current lobby key
        abort_unless(Session::has(Lobby::CACHE_KEY_CURRENT_LOBBY_ID), Response::HTTP_BAD_REQUEST, 'No lobby found');

        $minPlayers = GameSettings::getMinPlayers();
        $maxPlayers = GameSettings::getMaxPlayers();

        // get lobby
        $lobbyId = Session::get(Lobby::CACHE_KEY_CURRENT_LOBBY_ID);

        /** @var Lobby $lobby */
        $lobby = Lobby::query()
            ->with(['users'])
            ->where('uuid', '=', $lobbyId)
            ->take(1)
            ->first();

        // check if user is lobby owner
        $lobbyUsers = $lobby->getUsers();

        /** @var User $lobbyOwner */
        $lobbyOwner = $lobbyUsers->firstWhere('pivot.is_owner', '=', true);

        abort_if($lobbyOwner->getKey() !== $request->get('auth_user_id'), Response::HTTP_UNAUTHORIZED, 'You are not the lobby owner');

        abort_if($lobbyUsers->count() < $minPlayers, Response::HTTP_BAD_REQUEST, 'Not enough players to start, need >' . $minPlayers);
        abort_if($lobbyUsers->count() > $maxPlayers, Response::HTTP_BAD_REQUEST, 'Too many players to start, need <' . $maxPlayers);

        $game = new Game([
            'lobby_uuid' => $lobbyId,
        ]);

        $game->save();

        $lobbyUserIds = $lobbyUsers->pluck('uuid');
        $game->users()->sync($lobbyUserIds);

        try {
            (new GameState($game, $request->getGameSettings()))
                ->save();
        }
        catch (InvalidGameSettingsException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        // todo send websocket msg to other players

        return response()->json($game);
    }
}
