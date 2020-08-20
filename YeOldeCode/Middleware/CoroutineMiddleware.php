<?php

declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReactiveApps\Command\HttpServer\RequestHandlerFactory;
use Recoil\Kernel;
use Throwable;

use function array_key_exists;
use function React\Promise\resolve;

/**
 * @internal
 */
final class CoroutineMiddleware
{
    private Kernel $kernel;

    private RequestHandlerFactory $requestHandlerFactory;

    public function __construct(Kernel $kernel, RequestHandlerFactory $requestHandlerFactory)
    {
        $this->kernel                = $kernel;
        $this->requestHandlerFactory = $requestHandlerFactory;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $requestHandlerAnnotations = $request->getAttribute('request-handler-annotations');

        if (array_key_exists('coroutine', $requestHandlerAnnotations) && $requestHandlerAnnotations['coroutine'] === true) {
            return $this->runCoroutine($request);
        }

        return resolve($next($request));
    }

    private function runCoroutine(ServerRequestInterface $request): PromiseInterface
    {
        return new Promise(function (callable $resolve, callable $reject) use ($request): void {
            $requestHandler = $this->requestHandlerFactory->create($request);

            $this->kernel->execute(static function () use ($requestHandler, $request, $resolve, $reject) {
                try {
                    $resolve(yield $requestHandler($request));
                } catch (Throwable $throwable) {
                    $reject($throwable);
                }
            });
        });

        return $this->wrapper->call($requestHandler, $request);
    }
}
