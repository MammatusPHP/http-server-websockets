<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration\WebSocket;

use function assert;

final class Subscription
{
    private string $name;
    private string $command;

    public function __construct(string $name, string $command)
    {
        $this->name = $name;
        $this->command = $command;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function command(): string
    {
        return $this->command;
    }
}
