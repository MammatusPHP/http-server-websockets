<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\HealthCheck;

use Chimera\Mapping\Routing\FetchEndpoint;
use Mammatus\Http\Server\Annotations\Bus;
use Mammatus\Http\Server\Annotations\Vhost;
use Mammatus\Http\Server\Annotations\WebSocket\Realm;
use Mammatus\Http\Server\Annotations\WebSocket\Rpc;

/**
 * @Vhost("healthz")
 * @Bus("healthz")
 * @Realm("healthz")
 * @Rpc(rpc="healthz", command=FetchHealthz::class)
 * @FetchEndpoint(path="/healthz", query=FetchHealthz::class, name="FetchHealtz")
 */
final class HealthzHandler
{
    public function handle(FetchHealthz $request): HealthResult
    {
        return new HealthResult('healthy');
    }
}
