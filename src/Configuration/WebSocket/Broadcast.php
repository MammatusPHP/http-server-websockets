<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration\WebSocket;

final class Broadcast
{
    private string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function class(): string
    {
        return $this->class;
    }
}
