<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game;
use App\State\Actions\DealCard;
use App\State\Actions\RevealTrumpCard;
use App\State\GameState;

class DealCardsCommand extends Command
{
    protected $signature = 'action:deal-cards {gameId} {numPerPlayer}';

    public function handle()
    {
        $gameId = $this->argument('gameId');

        $game = Game::find($gameId);

        $gameState = new GameState($game, null);

        for ($i = 0; $i < $this->argument('numPerPlayer'); $i++) {
            foreach ($gameState->getPlayersInDealingOrder() as $player) {
                (new DealCard())($player, $gameState->getCardShoeState());
            }
        }

        (new RevealTrumpCard())($gameState->getCurrentRound(), $gameState->getCardShoeState());

        $gameState->save();
    }
}
