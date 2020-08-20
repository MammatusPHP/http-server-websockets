<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration\WebSocket;

final class Bus
{
    private string $name;

    /** @var array<Handler> */
    private array $handlers = [];

    /**
     * @param array<Handler> $handlers
     */
    public function __construct(string $name, Handler ...$handlers)
    {
        $this->name     = $name;
        $this->handlers = $handlers;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function handlers(): array
    {
        return $this->handlers;
    }
}
