<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\GameState;
use App\State\PlayerState;

class DealForRound
{
    public function __construct(protected GameState $gameState)
    {
    }

    public function handle(): void
    {
        for ($i = 0; $i < $this->gameState->getCurrentRound()->getNumCards(); $i++) {
            /** @var PlayerState $player */
            foreach ($this->gameState->getPlayersInDealingOrder() as $player) {
                $player->addToHand($this->gameState->getCardShoeState()->dealCardOut());
            }
        }

        if ($this->gameState->getCurrentRound()->shouldDrawTrumpCard()) {
            $this->gameState->getCurrentRound()->setTrumpCard($this->gameState->getCardShoeState()->dealCardOut());
        }
    }
}
