<?php

namespace Destiny\Commerce;

abstract class PaymentStatus
{

    public const _NEW = 'New';
    public const ACTIVE = 'Active';
    public const PENDING = 'Pending';
    public const COMPLETED = 'Completed';
    public const ERROR = 'Error';

}