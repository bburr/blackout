<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game;
use App\State\GameState;

class DisplayGameStateCommand extends Command
{
    protected $signature = 'state:display {gameId}';

    public function handle(): void
    {
        $gameId = $this->argument('gameId');

        $game = Game::find($gameId);

        if ($game === null) {
            throw new \LogicException('Game not found');
        }

        $gameState = new GameState($game, null);
        $currentRound = $gameState->getCurrentRound();

        $bettingPlayer = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToBet());
        $nextPlayer = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToPlay());

        $headers = ['Game State', 'Value'];
        $rows = [
            ['Dealer', $gameState->getDealer()->getUser()->getKey()],
            ['Betting Player', $bettingPlayer ? $bettingPlayer->getUser()->getKey() : ''],
            ['Next Player', $nextPlayer ? $nextPlayer->getUser()->getKey() : ''],
            ['Round Number', $currentRound->getRoundNumber()],
            ['Num Cards', $currentRound->getNumTricks()],
            ['Num Cards Asc?', $currentRound->isNumTricksAscending()],
            ['Trump Card', $currentRound->getTrumpCard()],
        ];

        $this->table($headers, $rows);

        $headers = [];
        $rows = [];

        // todo update display to handle tricks
        $bets = $gameState->getCurrentRound()->getBets();
        $plays = $gameState->getCurrentRound()->getCurrentTrick()->getPlays();

        $playerIndex = 0;
        foreach ($gameState->getPlayers() as $player) {
            $headers[] = $player->getUser()->getKey();
            $rows[0][$playerIndex] = 'Bet: ' . ($bets[$playerIndex] ?? 'N/A');
            $rows[1][$playerIndex] = 'Played: ' . ($plays[$playerIndex] ?? 'N/A');

            $i = 2;

            foreach ($player->getHand() as $card) {
                $rows[$i][$playerIndex] = $card;

                $i++;
            }

            if (isset($plays[$playerIndex])) {
                $rows[$i][$playerIndex] = '';
            }

            $playerIndex++;
        }

        $this->table($headers, $rows);
    }
}
