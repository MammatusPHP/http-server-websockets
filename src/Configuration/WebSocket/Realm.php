<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\Configuration\WebSocket;

use function assert;

final class Realm
{
    private string $name;
    /** @var array<Rpc>  */
    private array $rpcs;
    /** @var array<Subscription>  */
    private array $subscriptions;
    /** @var array<string>  */
    private array $busses;

    /**
     * @param array<Rpc> $rpcs
     * @param array<Subscription> $subscriptions
     * @param array<string> $busses
     */
    public function __construct(string $name, array $rpcs, array $subscriptions, array $busses)
    {
        $this->name  = $name;
        $this->rpcs = $rpcs;
        $this->subscriptions = $subscriptions;
        $this->busses = $busses;
    }


    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return iterable<Rpc>
     */
    public function rpcs(): iterable
    {
        yield from $this->rpcs;
    }

    /**
     * @return iterable<Subscription>
     */
    public function subscriptions(): iterable
    {
        yield from $this->subscriptions;
    }

    /**
     * @return iterable<string>
     */
    public function busses(): iterable
    {
        yield from $this->busses;
    }
}
