<?php

declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\React\Parallel\PoolInterface;

use function array_key_exists;
use function React\Promise\resolve;
use function WyriHaximus\psr7_response_decode;
use function WyriHaximus\psr7_response_encode;
use function WyriHaximus\psr7_server_request_decode;
use function WyriHaximus\psr7_server_request_encode;

/**
 * @internal
 */
final class ThreadMiddleware
{
    private PoolInterface $pool;

    public function __construct(PoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $requestHandlerAnnotations = $request->getAttribute('request-handler-annotations');

        if (array_key_exists('thread', $requestHandlerAnnotations) && $requestHandlerAnnotations['thread'] === true) {
            return $this->pool->run(static function ($jsonRequest) {
                $request        = psr7_server_request_decode($jsonRequest);
                $requestHandler = $request->getAttribute('request-handler');
                $response       = $requestHandler($request);

                return psr7_response_encode($response);
            }, [psr7_server_request_encode($request)])->then(static function ($response) {
                return psr7_response_decode($response);
            });
        }

        return resolve($next($request));
    }
}
