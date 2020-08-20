<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Web;

use League\Tactician\Middleware;
use Mammatus\Http\Server\CommandBus\Result as CommandBusResult;
use Mammatus\Http\Server\WebSockets\Result\String_;
use Psr\Http\Message\ResponseInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class ResponseTransformerMiddleware implements Middleware
{
    public function execute($command, callable $next): ResponseInterface
    {
        return $this->extractResult($next($command));
    }

    private function extractResult(object $result): ResponseInterface
    {
        if ($result instanceof CommandBusResult) {
            return $result->response();
        }

        return $result;
    }
}
