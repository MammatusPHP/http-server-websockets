<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration;

use function assert;

final class Server
{
    private Vhost $vhost;
    /** @var array<Bus>  */
    private array $busses;

    public function __construct(Vhost $vhost, Bus ...$busses)
    {
        $this->vhost  = $vhost;
        $this->busses = $busses;
    }

    /**
     * @return iterable<Bus>
     */
    public function busses(): iterable
    {
        yield from $this->busses;
    }

    public function vhost(): Vhost
    {
        return $this->vhost;
    }

    public function hasWebroot(): bool
    {
        return $this->vhost->webroot() instanceof WebrootPath;
    }

    public function webroot(): string
    {
        assert($this->vhost->webroot() instanceof WebrootPath);

        return $this->vhost->webroot()->path();
    }
}
