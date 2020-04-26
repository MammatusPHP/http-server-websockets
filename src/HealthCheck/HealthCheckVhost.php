<?php declare(strict_types=1);

namespace Mammatus\Http\Server\HealthCheck;

use Mammatus\Http\Server\Configuration\Vhost;
use Mammatus\Http\Server\Configuration\Webroot;
use Mammatus\Http\Server\Webroot\NoWebroot;

final class HealthCheckVhost implements Vhost
{
    private const SERVER_NAME = 'healtz';
    private const LISTEN_PORT = 9666;

    public function port(): int
    {
        return self::LISTEN_PORT;
    }

    public function name(): string
    {
        return self::SERVER_NAME;
    }

    public function webroot(): Webroot
    {
        return new NoWebroot();
    }
}
