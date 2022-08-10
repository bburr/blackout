<?php declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class InvalidTrickNumberSettingsException extends InvalidGameSettingsException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('The %s number of tricks must be less than or equal to the maximum number of tricks', $message), $code, $previous);
    }
}
