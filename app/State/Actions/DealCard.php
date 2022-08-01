<?php declare(strict_types=1);

namespace App\State\Actions;

use App\State\CardShoeState;
use App\State\PlayerState;

class DealCard
{
    public function __invoke(PlayerState $recipient, CardShoeState $cardShoeState): void
    {
        $recipient->addToHand($cardShoeState->dealCardOut());
    }
}
