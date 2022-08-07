<?php declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\GameIsCompleteException;
use App\State\GameState;
use App\State\RoundScoreState;
use Illuminate\Support\Facades\Bus;

class NextRound
{
    public function __construct(protected GameState $gameState)
    {
    }

    /**
     * @throws GameIsCompleteException
     */
    public function handle(): void
    {
        $currentRound = $this->gameState->getCurrentRound();
        $gameSettings = $this->gameState->getGameSettings();

        // todo score current round and save to $previousRounds
        $this->gameState->addPreviousRound(new RoundScoreState());

        if ($currentRound->isNumTricksAscending()) {
            $numTricks = $currentRound->getNumTricks() + 1;

            if ($numTricks === $gameSettings->getMaxNumTricks()) {
                $isNumTricksAscending = false;
            }
        }
        else {
            $numTricks = $currentRound->getNumTricks() - 1;

            if ($numTricks < $gameSettings->getEndingNumTricks()) {
                throw new GameIsCompleteException();
            }
        }

        $this->gameState->advanceDealerIndex();

        Bus::dispatch(new StartRound($this->gameState, $currentRound->getRoundNumber() + 1, $numTricks, $isNumTricksAscending ?? $currentRound->isNumTricksAscending()));
    }
}
