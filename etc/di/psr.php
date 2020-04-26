<?php declare(strict_types=1);

use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

return [
    ResponseFactoryInterface::class => new ResponseFactory(),
];
