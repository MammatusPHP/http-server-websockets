<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\WebSockets;

use Chimera\Input;

final class RpcInput implements Input
{
    private ?object $data;

    /** @phpstan-ignore-next-line  */
    public function __construct(?object $data)
    {
        $this->data = $data;
    }

    /**
     * @param ?mixed $default
     *
     * @return null
     *
     * @phpstan-ignore-next-line
     */
    public function getAttribute(string $name, $default = null)
    {
        return null;
    }

    /** @phpstan-ignore-next-line  */
    public function data(): ?object
    {
        return $this->data;
    }

    /**
     * @deprecated
     *
     * @return array<int, ?object>
     */
    public function getData(): array
    {
        return [$this->data];
    }
}
