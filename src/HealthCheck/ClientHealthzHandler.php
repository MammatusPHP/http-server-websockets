<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\HealthCheck;

use Chimera\Mapping\Routing\FetchEndpoint;
use Mammatus\Http\Server\Annotations\Bus;
use Mammatus\Http\Server\Annotations\Vhost;
use Mammatus\Http\Server\Annotations\WebSocket\Realm;
use Mammatus\Http\Server\Annotations\WebSocket\Rpc;
use Mammatus\Http\Server\Annotations\WebSocket\Subscription;

/**
 * @Vhost("healthz")
 * @Subscription(bus="healthz", realm="healthz", topic="client_healthz", command=ReceiveHealthz::class)
 */
final class ClientHealthzHandler
{
    public function handle(ReceiveHealthz $healthz): void
    {
        echo 'A client resports to be healthy, yay!', PHP_EOL;
    }
}
