<?php

declare(strict_types=1);

namespace Mammatus\Http\Server\WebSockets;

use Exception;

final class UnknownRealmException extends Exception
{
    /** @psalm-suppress MissingConstructor */
    private string $realm;

    public static function create(string $realm): self
    {
        $self        = new self('Unknown Realm: ' . $realm);
        $self->realm = $realm;

        return $self;
    }

    public function realm(): string
    {
        return $this->realm;
    }
}
