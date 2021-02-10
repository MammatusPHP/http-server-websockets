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
     * @return mixed|null
     *
     * @phpstan-ignore-next-line
     */
    public function getAttribute(string $name, $default = null)
    {
        if ($this->data instanceof HasAttributes) {
            if ($this->data->has($name)) {
                return $this->data->get($name);
            }
        }

        return $default;
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
