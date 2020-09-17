<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\WebSockets\Result;

use Mammatus\Http\Server\WebSockets\Result as ResultContract;

// phpcs:disable
final class Array_ implements ResultContract
{
    /** @var array<mixed> */
    private array $array;

    /**
     * @param array<mixed> $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->array;
    }
}
