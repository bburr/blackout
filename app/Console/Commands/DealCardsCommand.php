<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\DealForRound;
use App\Models\Game;
use App\State\GameState;
use Illuminate\Support\Facades\Bus;

class DealCardsCommand extends Command
{
    protected $signature = 'action:deal-cards {gameId}';

    public function handle()
    {
        $gameId = $this->argument('gameId');

        $game = Game::find($gameId);

        $gameState = new GameState($game, null);

        Bus::dispatch(new DealForRound($gameState));

        $gameState->save();
    }
}
