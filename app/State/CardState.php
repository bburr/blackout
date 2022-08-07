<?php declare(strict_types=1);

namespace App\State;

/**
 * @phpstan-type SerializedCardState array{suit: string, value: int}
 */
class CardState extends AbstractState
{
    public function __construct(protected string $suitKey, protected int $valueKey)
    {
    }

    public function __toString(): string
    {
        return static::listValues()[$this->valueKey] . ' of ' . static::listSuits()[$this->suitKey];
    }

    public function getSuitKey(): string
    {
        return $this->suitKey;
    }

    public function getValueKey(): int
    {
        return $this->valueKey;
    }

    /**
     * @phpstan-return SerializedCardState
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'suit' => $this->suitKey,
            'value' => $this->valueKey,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function listSuits(): array
    {
        return [
            'D' => 'Diamonds',
            'C' => 'Clubs',
            'H' => 'Hearts',
            'S' => 'Spades',
        ];
    }

    /**
     * @return string[]
     */
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
