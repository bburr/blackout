<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\GameState;

class DealForRound
{
    public function __construct(protected GameState $gameState)
    {
    }

    public function handle()
    {
        for ($i = 0; $i < $this->gameState->getCurrentRound()->getNumCards(); $i++) {
            foreach ($this->gameState->getPlayersInDealingOrder() as $player) {
                $player->addToHand($this->gameState->getCardShoeState()->dealCardOut());
            }
        }

        $this->gameState->getCurrentRound()->setTrumpCard($this->gameState->getCardShoeState()->dealCardOut());
    }
}
