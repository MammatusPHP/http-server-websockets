<?php declare(strict_types=1);

namespace Mammatus\Http\Server\Web;

use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;

final class Server
{
    private string $name;
    private string $address;
    /** @psalm-suppress PropertyNotSetInConstructor */
    private ?SocketServer $socket = null;
    private HttpServer $http;

    /**
     * @param string $name
     * @param string $address
     * @param HttpServer $http
     */
    public function __construct(string $name, string $address, HttpServer $http)
    {
        $this->name = $name;
        $this->address = $address;
        $this->http = $http;
    }

    public function start(LoopInterface $loop)
    {
        $this->socket = new SocketServer($this->address, $loop);
        $this->http->listen($this->socket);
    }

    public function stop()
    {
        if (! ($this->socket instanceof SocketServer)) {
            return;
        }

        $this->socket->close();
    }

    public function name(): string
    {
        return $this->name;
    }
}
