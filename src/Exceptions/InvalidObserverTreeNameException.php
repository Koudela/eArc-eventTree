<?php

namespace eArc\eventTree\Exceptions;

use Throwable;

class InvalidObserverTreeNameException extends \InvalidArgumentException
{
    public function __construct(string $treeName = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Name `$treeName` does not point to an ObserverTree", $code, $previous);
    }
}
