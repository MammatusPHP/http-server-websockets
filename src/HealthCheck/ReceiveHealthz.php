<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\HealthCheck;

use Chimera\Input;

final class ReceiveHealthz
{
    public static function fromInput(Input $input): ReceiveHealthz
    {
        return new self();
    }
}
