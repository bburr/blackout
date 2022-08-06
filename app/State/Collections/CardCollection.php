<?php declare(strict_types=1);

namespace App\State\Collections;

use App\State\CardState;
use Illuminate\Support\Collection;

/**
 * @extends Collection<int, CardState>
 * @phpstan-import-type SerializedCardState from CardState
 * @phpstan-type SerializedCardCollection array<int, SerializedCardState>
 */
class CardCollection extends Collection
{
    /**
     * @return array
     * @phpstan-return SerializedCardCollection
     */
    public function jsonSerialize(): array
    {
        return parent::jsonSerialize();
    }
}
