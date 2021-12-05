<?php
declare(strict_types=1);

namespace Destiny\Commerce;

abstract class OrderStatus
{

    public const _NEW = 'New';
    public const ERROR = 'Error';
    public const COMPLETED = 'Completed';
    public const PENDING = 'Pending';

}