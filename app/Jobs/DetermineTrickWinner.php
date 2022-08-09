<?php declare(strict_types=1);

namespace App\Jobs;

use App\State\CardState;
use App\State\Collections\CardCollection;

class DetermineTrickWinner
{
    public function __construct(protected ?CardState $trumpCard, protected CardState $leadingCard, protected CardCollection $plays)
    {
    }

    public function handle(): int
    {
        $highestCard = $this->leadingCard;

        /** @var CardState $play */
        foreach ($this->plays as $play) {
            if ((string) $play === (string) $this->leadingCard) {
                continue;
            }

            if ($play->getSuitKey() === $this->leadingCard->getSuitKey()
                && $play->getSuitKey() === $highestCard->getSuitKey()
                && $play->getValueKey() > $highestCard->getValueKey()) {
                $highestCard = $play;
            }

            if ($this->trumpCard !== null
                && $play->getSuitKey() === $this->trumpCard->getSuitKey()
                && ($play->getSuitKey() !== $highestCard->getSuitKey() || $play->getValueKey() > $highestCard->getValueKey())) {
                $highestCard = $play;
            }
        }

        $card = $this->plays->search($highestCard);

        if (! is_int($card)) {
            throw new \LogicException('Could not determine trick winner');
        }

        return $card;
    }
}
