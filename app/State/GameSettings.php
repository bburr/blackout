<?php declare(strict_types=1);

namespace App\State;

class GameSettings extends AbstractState
{
    public function getEndingNumCards(): int
    {
        // todo
        return 3;
    }

    public function getMaxNumCards(): int
    {
        // todo
        return 5;
    }

    public function getMaxPlayers(): int
    {
        // todo
        return 5;
    }

    public function getMinPlayers(): int
    {
        // todo
        return 3;
    }

    public function getNumDecks(): int
    {
        // todo
        return 1;
    }

    public function getStartingNumCards(): int
    {
        // todo
        return 3;
    }

    public function jsonSerialize()
    {
        // todo
        return [];
    }
}
