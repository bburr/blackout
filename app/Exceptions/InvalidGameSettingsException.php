<?php declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

abstract class InvalidGameSettingsException extends AbstractException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Invalid game settings - ' . $message, $code, $previous);
    }
}
