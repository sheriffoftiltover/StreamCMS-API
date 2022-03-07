<?php

declare(strict_types=1);

namespace StreamCMS\User\Controllers\AccountProviders;

use StreamCMS\User\Models\Account;

/**
 * These convert various account providers into a StreamCMS account.
 * Class AbstractAccountProvider
 * @package StreamCMS\User\Controllers\AccountProviders
 */
abstract class AbstractAccountProvider
{
    abstract public function getAccount(): Account;
}
