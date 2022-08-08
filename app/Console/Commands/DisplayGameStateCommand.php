<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game;
use App\State\GameState;
use App\State\RoundScoreState;
use App\State\TrickState;

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
            ['Leading Player', $gameState->getPlayerAtIndex($gameState->getLeadingPlayerIndex())?->getUser()->getKey()],
            ['Betting Player', $bettingPlayer ? $bettingPlayer->getUser()->getKey() : ''],
            ['Next Player', $nextPlayer ? $nextPlayer->getUser()->getKey() : ''],
            ['Round Number', $currentRound->getRoundNumber()],
            ['Num Cards', $currentRound->getNumTricks()],
            ['Num Cards Asc?', $currentRound->isNumTricksAscending() ? 'Y' : 'N'],
            ['Trump Card', $currentRound->getTrumpCard()],
            ['Leading Card', $currentRound->getCurrentTrick()->getLeadingCard()],
        ];

        $this->table($headers, $rows);

        $headers = [];
        $rows = [];

        // display current trick state
        $bets = $gameState->getCurrentRound()->getBets();
        $plays = $gameState->getCurrentRound()->getCurrentTrick()->getPlays();

        $trickWinners = [];

        /** @var TrickState $previousTrick */
        foreach ($gameState->getCurrentRound()->getPreviousTricks() as $previousTrick) {
            $trickWinners[$previousTrick->getTrickWinnerIndex()] = isset($trickWinners[$previousTrick->getTrickWinnerIndex()])
                ? $trickWinners[$previousTrick->getTrickWinnerIndex()] + 1 : 1;
        }

        foreach ($gameState->getPlayers() as $playerIndex => $player) {
            $headers[] = $player->getUser()->getKey();
            $rows[0][$playerIndex] = 'Player Index: ' . $playerIndex;
            $rows[1][$playerIndex] = 'Bet: ' . ($bets[$playerIndex] ?? 'N/A');
            $rows[2][$playerIndex] = 'Tricks: ' . ($trickWinners[$playerIndex] ?? '0');
            $rows[3][$playerIndex] = 'Played: ' . ($plays[$playerIndex] ?? 'N/A');
            $rows[4][$playerIndex] = '';

            $i = 5;

            foreach ($player->getHand() as $card) {
                $rows[$i][$playerIndex] = $card;

                $i++;
            }

            if (isset($plays[$playerIndex])) {
                $rows[$i][$playerIndex] = '';
            }
        }

        $this->table($headers, $rows);

        $rows = [];

        /** @var RoundScoreState $roundScore */
        foreach ($gameState->getPreviousRoundScores() as $roundScore) {
            $index = $roundScore->getRoundNumber() - 1;

            $rows[$index][0] = $roundScore->getRoundNumber();

            foreach ($roundScore->getScores() as $playerIndex => $score) {
                $rows[$index][$playerIndex + 1] = $score;
            }

            ksort($rows[$index]);
        }

        array_unshift($headers, 'Round');

        $this->table($headers, $rows);
    }
}
