<?php declare(strict_types=1);

namespace App\State\Collections;

use App\State\CardState;
use App\State\TrickState;
use Illuminate\Support\Collection;

/**
 * @extends Collection<int, TrickState>
 * @phpstan-import-type SerializedTrickState from TrickState
 * @phpstan-type SerializedTrickCollection array<int, SerializedTrickState>
 */
class TrickCollection extends Collection
{
    /**
     * @return array
     * @phpstan-return SerializedTrickCollection
     */
    public function jsonSerialize(): array
    {
        return parent::jsonSerialize();
    }
}
