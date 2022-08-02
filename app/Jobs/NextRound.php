<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\GameState;
use App\State\RoundScoreState;
use Illuminate\Support\Facades\Bus;

class NextRound
{
    public function __construct(protected GameState $gameState)
    {
    }

    public function handle()
    {
        $currentRound = $this->gameState->getCurrentRound();
        $gameSettings = $this->gameState->getGameSettings();

        // todo score current round and save to $previousRounds
        $this->gameState->addPreviousRound(new RoundScoreState());

        if ($currentRound->isNumCardsAscending()) {
            $numCards = $currentRound->getNumCards() + 1;

            if ($numCards === $gameSettings->getMaxNumCards()) {
                $isNumCardsAscending = false;
            }
        }
        else {
            $numCards = $currentRound->getNumCards() - 1;

            if ($numCards < $gameSettings->getEndingNumCards()) {
                throw new \LogicException('Game is over, cannot advance past this round');
            }
        }

        $this->gameState->advanceDealerIndex();

        Bus::dispatch(new StartRound($this->gameState, $currentRound->getRoundNumber() + 1, $numCards, $isNumCardsAscending ?? $currentRound->isNumCardsAscending()));
    }
}
