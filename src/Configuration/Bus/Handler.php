<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration\Bus;

final class Handler
{
    private string $bus;
    private string $command;
    private string $handler;

    public function __construct(string $bus, string $command, string $handler)
    {
        $this->command        = $command;
        $this->handler        = $handler;
    }

    public function bus(): string
    {
        return $this->bus;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function handler(): string
    {
        return $this->handler;
    }
}
