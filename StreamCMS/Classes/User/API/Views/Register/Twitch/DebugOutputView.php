<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Views\Register\Twitch;

use StreamCMS\Utility\API\Views\AbstractJsonView;

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
