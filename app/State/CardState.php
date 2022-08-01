<?php declare(strict_types=1);

namespace App\State;

class CardState extends AbstractState
{
    public function __construct(protected string $suitKey, protected int $valueKey)
    {
    }

    public function __toString(): string
    {
        return static::listValues()[$this->valueKey] . ' of ' . static::listSuits()[$this->suitKey];
    }

    public function jsonSerialize()
    {
        return [
            'suit' => $this->suitKey,
            'value' => $this->valueKey,
        ];
    }

    public static function listSuits(): array
    {
        return [
            'D' => 'Diamonds',
            'C' => 'Clubs',
            'H' => 'Hearts',
            'S' => 'Spades',
        ];
    }

    public static function listValues(): array
    {
        return [
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            'J',
            'Q',
            'K',
            'A',
        ];
    }
}
