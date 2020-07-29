<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Middleware;

use parallel\Channel;
use Psr\Http\Message\ServerRequestInterface;

final class Request
{
    private ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }
}
