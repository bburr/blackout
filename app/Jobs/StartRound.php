<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\CardShoeState;
use App\State\GameSettings;
use App\State\GameState;
use App\State\RoundState;
use Illuminate\Support\Facades\Bus;

class StartRound
{

    public function __construct(protected GameState $gameState, protected int $roundNumber, protected int $numTricks, protected bool $isNumTricksAscending)
    {
    }

    public function handle(): void
    {
        $playerIndexAfterDealer = $this->gameState->getPlayerIndexAfter($this->gameState->getDealerIndex());

        $this->gameState->setCurrentRound(new RoundState(
            $this->roundNumber,
            $this->numTricks,
            $this->isNumTricksAscending,
            $playerIndexAfterDealer,
            $playerIndexAfterDealer
        ));

        $this->gameState->setLeadingPlayerIndex($playerIndexAfterDealer);

        $this->gameState->setShoe(new CardShoeState(GameSettings::getNumDecks()));

        Bus::dispatch(new DealForRound($this->gameState));
    }
}
