<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Player\GetHand;
use App\Http\Requests\Player\GetUserHand;
use App\Models\Game;
use App\State\GameState;
use App\State\PlayerState;
use Symfony\Component\HttpFoundation\Response;

class PlayerController extends Controller
{
    public function getHand(GetHand $request): Response
    {
        /** @var Game|null $game */
        $game = Game::find($request->get('game_id'));

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game, null);

        $player = $gameState->getPlayers()->firstWhere(fn (PlayerState $playerState) => $playerState->getUser()->getKey() === $request->get('auth_user_id'));

        abort_if($player === null, Response::HTTP_BAD_REQUEST, 'You are not in that game');

        return response()->json($player->getHand());
    }

    public function getUserHand(GetUserHand $request): Response
    {
        return $this->getHand($request);
    }
}
