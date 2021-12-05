<?php

namespace Destiny\Commerce;

abstract class PaymentProfileStatus
{

    public const ERROR = 'Error';
    public const ACTIVEPROFILE = 'ActiveProfile';
    public const CANCELLEDPROFILE = 'CancelledProfile';

}