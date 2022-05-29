<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\Views;

class DebugOutputView extends AbstractJsonView
{
    public function __construct(protected array $debugOutput)
    {
    }

    public function toArray(): array
    {
        return $this->debugOutput;
    }
}
