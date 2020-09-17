<?php

declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\WebSockets;

use Ancarda\Psr7\StringStream\ReadOnlyStringStream;
use Mammatus\Http\Server\WebSockets\ResponseTransformerMiddleware;
use RingCentral\Psr7\Response;
use stdClass;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

use function Safe\json_encode;

final class ResponseTransformerMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function response(): void
    {
        $response   = (new Response())->withBody(new ReadOnlyStringStream('body'));
        $middleware = new ResponseTransformerMiddleware();

        $result = $middleware->execute(new stdClass(), static fn () => $response);

        self::assertSame('body', json_encode($result));
    }
}
