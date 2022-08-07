<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\GameState;

class MakeBetForNextPlayer
{
    public function __construct(protected GameState $gameState, protected int $bet)
    {
    }

    public function handle(): void
    {
        $this->gameState->getCurrentRound()->makeBetForNextPlayer($this->bet);
        $bettingPlayerIndex = $this->gameState->getCurrentRound()->getNextPlayerIndexToBet();
        $this->gameState->getCurrentRound()->setNextPlayerIndexToBet($this->gameState->advancePlayerIndexUntilLeadingPlayer($bettingPlayerIndex));
    }
}
