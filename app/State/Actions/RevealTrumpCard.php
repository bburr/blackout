<?php declare(strict_types=1);

namespace App\State\Actions;

use App\State\CardShoeState;
use App\State\RoundState;

class RevealTrumpCard
{
    public function __invoke(RoundState $roundState, CardShoeState $cardShoeState): void
    {
        $roundState->setTrumpCard($cardShoeState->dealCardOut());
    }
}
