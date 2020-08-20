<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\WebSockets\Result;

use Mammatus\Http\Server\WebSockets\Result;

final class Array_ implements Result
{
    private array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function jsonSerialize(): array
    {
        return $this->array;
    }
}
