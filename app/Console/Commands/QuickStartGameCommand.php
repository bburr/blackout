<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\MakeBetForNextPlayer;
use App\Models\Game;
use App\Models\Lobby;
use App\Models\User;
use App\State\GameSettings;
use App\State\GameState;
use Illuminate\Support\Facades\Bus;

class QuickStartGameCommand extends Command
{
    protected $signature = 'game:quickstart';

    public function handle(): void
    {
        $names = ['Alice', 'Bob', 'Charlie'];

        $owner = $this->createUser($names[0]);

        $lobby = new Lobby();
        $lobby->save();

        $lobby->users()->attach((string) $owner->getKey(), ['is_owner' => true]);

        for ($i = 1; $i < count($names); $i++) {
            $user = $this->createUser($names[$i]);
            $lobby->users()->attach((string) $user->getKey());
        }

        $game = new Game([
            'lobby_uuid' => $lobby->getKey(),
        ]);

        $game->save();

        $lobbyUserIds = $lobby->users()->pluck('uuid');
        $game->users()->sync($lobbyUserIds);

        $gameState = (new GameState($game, [
            'starting_num_tricks' => 1,
            'max_num_tricks' => 5,
            'ending_num_tricks' => 1,
        ]));

        for ($i = 0; $i < count($names); $i++) {
            Bus::dispatch(new MakeBetForNextPlayer($gameState, 1));
        }

        $gameState->save();

        $this->info($game->getKey());
    }

    protected function createUser(string $name): User
    {
        $user = new User([
            'name' => $name,
        ]);

        $user->save();

        return $user;
    }
}
