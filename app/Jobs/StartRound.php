<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\CardShoeState;
use App\State\GameState;
use App\State\RoundState;
use Illuminate\Support\Facades\Bus;

class StartRound
{

    public function __construct(protected GameState $gameState, protected int $roundNumber, protected int $numCards, protected bool $isNumCardsAscending)
    {
    }

    public function handle(): void
    {
        $playerIndexAfterDealer = $this->gameState->getPlayerIndexAfter($this->gameState->getDealerIndex());

        $this->gameState->setCurrentRound(new RoundState(
            $this->roundNumber,
            $this->numCards,
            $this->isNumCardsAscending,
            $playerIndexAfterDealer,
            $playerIndexAfterDealer
        ));

        $this->gameState->setShoe(new CardShoeState($this->gameState->getGameSettings()->getNumDecks()));

        Bus::dispatch(new DealForRound($this->gameState));
    }
}
