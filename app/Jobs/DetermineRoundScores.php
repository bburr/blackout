<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\GameSettings;
use App\State\RoundState;
use App\State\TrickState;

class DetermineRoundScores
{
    public function __construct(protected RoundState $roundState, protected GameSettings $gameSettings)
    {
    }

    /**
     * @return int[]
     */
    public function handle(): array
    {
        $bets = $this->roundState->getBets();
        $tricks = $this->roundState->getPreviousTricks();

        $trickWinners = [];

        /** @var TrickState $trick */
        foreach ($tricks as $trick) {
            $trickWinners[$trick->getTrickWinnerIndex()] = isset($trickWinners[$trick->getTrickWinnerIndex()])
                ? $trickWinners[$trick->getTrickWinnerIndex()] + 1 : 1;
        }

        $scores = [];

        foreach ($bets as $playerIndex => $bet) {
            $scores[$playerIndex] = $bet === ($trickWinners[$playerIndex] ?? 0) ? $bet + $this->gameSettings->getPointsForCorrectBet() : 0;
        }

        return $scores;
    }
}
