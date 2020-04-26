<?php declare(strict_types=1);

namespace Mammatus\Http\Server\CommandBus;

use League\Tactician\Middleware;
use parallel\Channel;

final class CommandHandlerMiddlewareProxy implements Middleware
{
    private string $input;
    private string $output;

    public function __construct(string $input, string $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function execute($command, callable $next)
    {
        Channel::open($this->output)->send($command);
        return Channel::open($this->input)->recv();
    }
}
