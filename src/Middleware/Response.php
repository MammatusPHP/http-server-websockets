<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use ReactParallel\Pool\Worker\Work\Result;

final class Response implements Result
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function result(): ResponseInterface
    {
        return $this->response;
    }
}
