<?php declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Support\Facades\Bus;

class DetermineDealer
{
    /**
     * @param int[] $playerIndexes
     */
    public function __construct(protected array $playerIndexes)
    {
    }

    public function handle(): int
    {
        $rolls = [];

        foreach ($this->playerIndexes as $playerIndex) {
            $rolls[$playerIndex] = random_int(1, 20);
        }

        $highRolls = array_keys($rolls, max($rolls));

        if (count($highRolls) > 1) {
            return Bus::dispatch(new self($highRolls));
        }

        return $highRolls[0];
    }
}
