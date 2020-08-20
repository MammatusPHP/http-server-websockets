<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration\WebSocket;

final class Handler
{
    private string $command;
    private string $commandHandler;
    private ?string $rpc;
    private ?string $subscription;

    public function __construct(string $command, string $commandHandler, ?string $rpc, ?string $subscription)
    {
        $this->command = $command;
        $this->commandHandler = $commandHandler;
        $this->rpc = $rpc;
        $this->subscription = $subscription;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function commandHandler(): string
    {
        return $this->commandHandler;
    }

    public function rpc(): ?string
    {
        return $this->rpc;
    }

    public function subscription(): ?string
    {
        return $this->subscription;
    }
}
