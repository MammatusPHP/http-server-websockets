<?php

declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use ReactiveApps\Command\HttpServer\RequestHandlerFactory;

use function React\Promise\resolve;

/**
 * @internal
 */
final class RequestHandlerMiddleware
{
    private RequestHandlerFactory $requestHandlerFactory;

    public function __construct(RequestHandlerFactory $requestHandlerFactory)
    {
        $this->requestHandlerFactory = $requestHandlerFactory;
    }

    public function __invoke(ServerRequestInterface $request): PromiseInterface
    {
        return resolve(($this->requestHandlerFactory->create($request))($request));
    }
}
