<?php

declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Closure\MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

use function array_key_exists;
use function React\Promise\resolve;
use function WyriHaximus\psr7_response_decode;
use function WyriHaximus\psr7_response_encode;
use function WyriHaximus\psr7_server_request_decode;
use function WyriHaximus\psr7_server_request_encode;

/**
 * @internal
 */
final class ChildProcessMiddleware
{
    private PromiseInterface $pool;

    public function __construct(PromiseInterface $pool)
    {
        $this->pool = $pool;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $requestHandlerAnnotations = $request->getAttribute('request-handler-annotations');

        if (array_key_exists('childprocess', $requestHandlerAnnotations) && $requestHandlerAnnotations['childprocess'] === true) {
            return $this->runChildProcess($request);
        }

        return resolve($next($request));
    }

    private function runChildProcess(ServerRequestInterface $request): PromiseInterface
    {
        $jsonRequest = psr7_server_request_encode($request);
        $rpc         = MessageFactory::rpc($this->createChildProcessClosure($jsonRequest));

        return $this->pool->then(static function (PoolInterface $pool) use ($rpc) {
            return $pool->rpc($rpc);
        })->then(static function (Payload $payload) {
            $response = $payload->getPayload();

            return psr7_response_decode($response);
        });
    }

    /**
     * @param mixed[] $jsonRequest
     *
     * @codeCoverageIgnore
     */
    private function createChildProcessClosure(array $jsonRequest): Closure
    {
        return static function () use ($jsonRequest) {
            $request        = psr7_server_request_decode($jsonRequest);
            $requestHandler = $request->getAttribute('request-handler');
            $response       = $requestHandler($request);

            return psr7_response_encode($response);
        };
    }
}
