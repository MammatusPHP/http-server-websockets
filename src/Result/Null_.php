<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\WebSockets\Result;

use JsonSerializable;
use Mammatus\Http\Server\WebSockets\Result;

// phpcs:disable
final class Null_ implements Result
{
    public function jsonSerialize(): ?JsonSerializable
    {
        return null;
    }
}
