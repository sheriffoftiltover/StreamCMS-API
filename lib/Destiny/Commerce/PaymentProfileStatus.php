<?php
declare(strict_types=1);

namespace Destiny\Commerce;

abstract class PaymentProfileStatus
{

    public const ERROR = 'Error';
    public const ACTIVEPROFILE = 'ActiveProfile';
    public const CANCELLEDPROFILE = 'CancelledProfile';

}