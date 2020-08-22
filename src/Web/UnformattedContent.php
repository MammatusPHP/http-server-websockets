<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Web;

use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UnformattedContent implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (! $response instanceof UnformattedResponse) {
            return $response;
        }

        $unformattedContent = $response->getUnformattedContent();
        if ($unformattedContent instanceof ResponseInterface) {
            return $unformattedContent;
        }

        return $response;
    }
}
