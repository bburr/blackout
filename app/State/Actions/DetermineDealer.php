<?php declare(strict_types=1);

namespace App\State\Actions;

class DetermineDealer
{
    public function __invoke(array $playerIndexes): int
    {
        $rolls = [];

        foreach ($playerIndexes as $playerIndex) {
            $rolls[$playerIndex] = random_int(1, 20);
        }

        $highRolls = array_keys($rolls, max($rolls));

        if (count($highRolls) > 1) {
            return (new self())($highRolls);
        }

        return $highRolls[0];
    }
}
