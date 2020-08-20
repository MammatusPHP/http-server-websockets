<?php

declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Annotations;

use function current;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Method
{
    private string $method;

    /**
     * @param string[] $methods
     */
    public function __construct(array $methods)
    {
        $this->method = current($methods);
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
