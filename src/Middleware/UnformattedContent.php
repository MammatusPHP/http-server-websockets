<?php declare(strict_types=1);

namespace Mammatus\Http\Server\Middleware;

use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class UnformattedContent
{
    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $promise = $next($request);

        if (!$promise instanceof PromiseInterface) {
            $promise = resolve($promise);
        }

        return $promise->then(function (ResponseInterface $response): ResponseInterface {
            if (!$response instanceof UnformattedResponse) {
                return $response;
            }

            $unformattedContent = $response->getUnformattedContent();
            if ($unformattedContent instanceof ResponseInterface) {
                return $unformattedContent;
            }

            return $response;
        });
    }
}
