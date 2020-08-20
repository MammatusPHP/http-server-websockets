<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\WebSockets;

use Chimera\Input;

final class RpcInput implements Input
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getAttribute(string $name, $default = null)
    {
        return null;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
