<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\HealthCheck;

use Ancarda\Psr7\StringStream\ReadOnlyStringStream;
use Chimera\Mapping\Routing\FetchEndpoint;
use Laminas\Diactoros\Response;
use Mammatus\Http\Server\Annotations\Bus;
use Mammatus\Http\Server\Annotations\Vhost;
use Psr\Http\Message\ResponseInterface;

/**
 * @Vhost("healtz")
 * @Bus("healtz")
 * @FetchEndpoint(path="/healtz", query=FetchHealtz::class, name="FetchHealtz")
 */
final class HealtzHandler
{
    public function handle(FetchHealtz $request): ResponseInterface
    {
        return new Response(new ReadOnlyStringStream('healthy'), 200, ['Content-Type' => 'text/plain']);
    }
}
