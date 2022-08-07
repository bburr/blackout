<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\GameState;
use Illuminate\Support\Facades\Bus;

class NextTrick
{
    public function __construct(protected GameState $gameState)
    {
    }

    public function handle(): void
    {
        $currentTrick = $this->gameState->getCurrentRound()->getCurrentTrick();
        $this->gameState->getCurrentRound()->addPreviousTrick($currentTrick);

        if ($this->gameState->getCurrentRound()->isRoundDone()) {
            return;
        }

        $trumpCard = $this->gameState->getCurrentRound()->getTrumpCard();
        $leadingCard = $currentTrick->getLeadingCard();
        $plays = $currentTrick->getPlays();

        if ($leadingCard === null) {
            throw new \LogicException('Invalid state - attempting to go to next trick with no leading card');
        }

        $trickWinnerIndex = Bus::dispatch(new DetermineTrickWinner($trumpCard, $leadingCard, $plays));

        $this->gameState->setLeadingPlayerIndex($trickWinnerIndex);
        $this->gameState->getCurrentRound()->newTrick();
        $this->gameState->getCurrentRound()->setNextPlayerIndexToPlay($trickWinnerIndex);
    }
}
