<?php declare(strict_types=1);

namespace Mammatus\Http\Server\Websockets;

use Chimera\Mapping\Routing\FetchEndpoint;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use ReactiveApps\Command\HttpServer\Annotations\Template;
use ReactiveApps\Command\HttpServer\Exception\UnknownRealmException;
use WyriHaximus\React\Http\Middleware\TemplateResponse;
use function WyriHaximus\getIn;
use WyriHaximus\React\Http\Middleware\Session;
use WyriHaximus\React\Http\Middleware\SessionMiddleware;

final class FetchJWT
{
}
