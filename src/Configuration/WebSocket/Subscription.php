<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration\WebSocket;

use function assert;

final class Subscription
{
    private string $name;
    private string $command;
    private string $bus;

    public function __construct(string $name, string $command, string $bus)
    {
        $this->name = $name;
        $this->command = $command;
        $this->bus = $bus;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function bus(): string
    {
        return $this->bus;
    }
}
