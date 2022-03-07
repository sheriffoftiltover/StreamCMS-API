<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Views\Tokens;

use StreamCMS\API\Views\Tokens\AbstractTokenView;

class RefreshTokenView extends AbstractTokenView
{
    public function __construct(string $token)
    {
        // TODO: Move the token type to an enum.
        parent::__construct($token, 'Refresh');
    }
}
