<?php

declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Annotations;

use function current;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Template
{
    private string $template;

    /**
     * @param string[] $templates
     */
    public function __construct(array $templates)
    {
        $this->template = current($templates);
    }

    public function getTemplate(): string
    {
        return $this->template;
    }
}
