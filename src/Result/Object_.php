<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\WebSockets\Result;

use JsonSerializable;
use Mammatus\Http\Server\WebSockets\Result;

// phpcs:disable
final class Object_ implements Result
{
    private JsonSerializable $object;

    public function __construct(JsonSerializable $object)
    {
        $this->object = $object;
    }

    public function jsonSerialize(): JsonSerializable
    {
        return $this->object;
    }
}
