<?php

declare(strict_types=1);

namespace StreamCMS\API\Views\Tokens;

use StreamCMS\API\Views\AbstractJsonView;

abstract class AbstractTokenView extends AbstractJsonView
{
    public function __construct(protected string $token, protected string $tokenType)
    {
    }

    public function toArray(): array
    {
        return [
            'tokenType' => $this->tokenType,
            'token' => $this->token,
        ];
    }
}
