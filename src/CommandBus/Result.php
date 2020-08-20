<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\CommandBus;

use Mammatus\Http\Server\WebSockets\Result as WebSocketResult;
use Psr\Http\Message\ResponseInterface;

interface Result
{
    public function response(): ResponseInterface;
    public function result(): WebSocketResult;
}
