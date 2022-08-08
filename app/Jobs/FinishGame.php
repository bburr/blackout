<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\GameState;

class FinishGame
{
    public function __construct(protected GameState $gameState)
    {
    }

    public function handle(): void
    {
        $scores = [];

        // iterate through round scores and determine winner
        foreach ($this->gameState->getPreviousRoundScores() as $roundScore) {
            foreach ($roundScore->getScores() as $playerIndex => $score) {
                $scores[$playerIndex] = $score + ($scores[$playerIndex] ?? 0);
            }
        }

        $winnerIndexes = array_keys($scores, max($scores));

        if (count($winnerIndexes) > 1) {
            // todo handle tiebreaker
            throw new \LogicException('Tiebreaker not yet implemented');
        }

        $this->gameState->getGame()->winner()->associate($this->gameState->getPlayerAtIndex($winnerIndexes[0])?->getUser());
        $this->gameState->getGame()->save();
    }
}
