<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration;

final class Handler
{
    /** @var array<string>  */
    private array $methods;
    private string $command;
    private string $commandHandler;
    private string $handler;
    private string $path;

    /**
     * @param array<string> $methods
     */
    public function __construct(array $methods, string $command, string $commandHandler, string $handler, string $path)
    {
        $this->methods        = $methods;
        $this->command        = $command;
        $this->commandHandler = $commandHandler;
        $this->handler        = $handler;
        $this->path           = $path;
    }

    public function methods(): array
    {
        return $this->methods;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function commandHandler(): string
    {
        return $this->commandHandler;
    }

    public function handler(): string
    {
        return $this->handler;
    }

    public function path(): string
    {
        return $this->path;
    }
}
