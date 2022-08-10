<?php declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\CardShoeIsEmptyException;
use App\State\GameState;
use App\State\PlayerState;

class DealForRound
{
    public function __construct(protected GameState $gameState)
    {
    }

    public function handle(): void
    {
        for ($i = 0; $i < $this->gameState->getCurrentRound()->getNumTricks(); $i++) {
            /** @var PlayerState $player */
            foreach ($this->gameState->getPlayersInDealingOrder() as $player) {
                $player->addToHand($this->gameState->getCardShoeState()->dealCardOut());
            }
        }

        if ($this->gameState->getCurrentRound()->shouldDrawTrumpCard()) {
            try {
                $this->gameState->getCurrentRound()->setTrumpCard($this->gameState->getCardShoeState()->dealCardOut());
            }
            catch (CardShoeIsEmptyException $e) {
                // todo handle this situation beforehand when configuring trump/no trump for each round before game start
                \Log::debug('CardShoeIsEmpty', [
                    'num_tricks' => $this->gameState->getCurrentRound()->getNumTricks(),
                    'num_players' => $this->gameState->getPlayers()->count(),
                    'round_number' => $this->gameState->getCurrentRound()->getRoundNumber(),
                ]);
            }
        }
    }
}
