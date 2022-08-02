<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game;
use App\State\GameState;

class DisplayGameStateCommand extends Command
{
    protected $signature = 'state:display {gameId}';

    public function handle()
    {
        $gameId = $this->argument('gameId');

        $game = Game::find($gameId);

        $gameState = new GameState($game, null);
        $currentRound = $gameState->getCurrentRound();

        $headers = ['Game State', 'Value'];
        $rows = [
            ['Dealer', $gameState->getDealer()->getUser()->getKey()],
            ['Round Number', $currentRound->getRoundNumber()],
            ['Num Cards', $currentRound->getNumCards()],
            ['Num Cards Asc?', $currentRound->isNumCardsAscending()],
            ['Trump Card', $currentRound->getTrumpCard()],
        ];

        $this->table($headers, $rows);


        $headers = [];
        $rows = [];

        $playerIndex = 0;
        foreach ($gameState->getPlayersInDealingOrder() as $player) {
            $headers[] = $player->getUser()->getKey();

            $i = 0;
            foreach ($player->getHand() as $card) {
                $rows[$i][$playerIndex] = $card;

                $i++;
            }

            $playerIndex++;
        }

        $this->table($headers, $rows);
    }
}
