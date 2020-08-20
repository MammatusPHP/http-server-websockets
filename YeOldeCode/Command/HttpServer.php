<?php

declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Command;

use Generator;
use Psr\Log\LoggerInterface;
use React\Http\StreamingServer as ReactHttpServer;
use React\Socket\ServerInterface as SocketServerInterface;
use ReactiveApps\Command\Command;
use ReactiveApps\LifeCycleEvents\Promise\Shutdown;
use WyriHaximus\Annotations\Coroutine;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;

use function React\Promise\resolve;

/**
 * @Coroutine()
 */
final class HttpServer implements Command
{
    public const COMMAND = 'http-server';

    private LoggerInterface $logger;

    private SocketServerInterface $socket;

    /** @var callable[] */
    private array $middleware;

    private Shutdown $shutdownEventPromise;

    /**
     * @param callable[] $middleware
     */
    public function __construct(LoggerInterface $logger, SocketServerInterface $socket, array $middleware, Shutdown $shutdownEventPromise)
    {
        $this->logger               = $logger;
        $this->socket               = $socket;
        $this->middleware           = $middleware;
        $this->shutdownEventPromise = $shutdownEventPromise;
    }

    public function __invoke(): Generator
    {
        $this->logger->debug('Creating HTTP server');
        $httpServer = new ReactHttpServer($this->middleware);
        $httpServer->on('error', CallableThrowableLogger::create($this->logger));

        $this->logger->debug('Creating HTTP server socket');
        $httpServer->listen($this->socket);
        $this->logger->debug('Listening for incoming requests');

        yield resolve($this->shutdownEventPromise);

        return 0;
    }
}
