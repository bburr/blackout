<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\GameStarted;
use App\Exceptions\InvalidGameSettingsException;
use App\Http\Requests\Game\StartGame;
use App\Models\Game;
use App\Models\Lobby;
use App\Models\User;
use App\State\GameSettings;
use App\State\GameState;
use App\State\PlayerState;
use App\State\TrickState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function game(Game $game, Request $request): InertiaResponse
    {
        $gameState = new GameState($game);

        $player = $gameState->getPlayers()->firstWhere(fn (PlayerState $playerState) => $playerState->getUser()->getKey() === $request->get('auth_user_id'));

        abort_if($player === null, Response::HTTP_BAD_REQUEST, 'You are not in that game');

        $users = $game->getLobby()->getUsers();

        $currentRound = $gameState->getCurrentRound();
        $currentTrick = $currentRound->getCurrentTrick();

        $scoreTotals = [];

        foreach ($gameState->getPreviousRoundScores() as $previousRoundScore) {
            foreach ($previousRoundScore->getScores() as $playerIndex => $score) {
                $scoreTotals[$playerIndex] = ($scoreTotals[$playerIndex] ?? 0) + $score;
            }
        }

        $tricksWon = [];

        /** @var TrickState $previousTrick */
        foreach ($currentRound->getPreviousTricks() as $previousTrick) {
            $tricksWon[$previousTrick->getTrickWinnerIndex()] = ($tricksWon[$previousTrick->getTrickWinnerIndex()] ?? 0) + 1;
        }

        return Inertia::render('Game', [
            'gameId' => $game->getKey(),
            'playerIndex' => $gameState->getPlayers()->search(fn (PlayerState $playerState) => $playerState->getUser()->getKey() === $player->getUser()->getKey()),
            'dealerIndex' => $gameState->getDealerIndex(),
            'nextPlayerIndexToBet' => $currentRound->getNextPlayerIndexToBet(),
            'nextPlayerIndexToPlay' => $currentRound->getNextPlayerIndexToPlay(),
            'players' => $gameState->getPlayers()->map(function (PlayerState $playerState, int $index) use ($users) {
                $user = $users->find($playerState->getUser()->getKey());
                return [
                    'index' => $index,
                    'name' => $user->getName(),
                ];
            }),
            'currentRound' => [
                'config' => [
                    'roundNumber' => $currentRound->getRoundNumber(),
                    'numTricks' => $currentRound->getNumTricks(),
                ],
                'trumpCard' => $currentRound->getTrumpCard(),
                'bets' => $currentRound->getBets(),
                'currentTrick' => [
                    'leadingCard' => $currentTrick->getLeadingCard(),
                    'plays' => $currentTrick->getPlays(),
                ],
                'tricksWon' => $tricksWon,
            ],
            'playerHand' => $player->getHand(),
            'scoreTotals' => $scoreTotals,
        ]);
    }

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

        broadcast(new GameStarted($lobby, $game))->toOthers();

        return Redirect::route('game', ['game' => $game->getKey()]);
    }
}
