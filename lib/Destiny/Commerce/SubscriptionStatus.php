<?php

namespace Destiny\Commerce;

abstract class SubscriptionStatus
{

    /**
     * Used for when a new sub is created before the order has cleared
     */
    public const _NEW = 'New';
    /**
     * Active and enabled subscription
     */
    public const ACTIVE = 'Active';
    /**
     * When a sub is waiting for the order to clear automatically
     */
    public const PENDING = 'Pending';
    /**
     * When the sub end date has passed
     */
    public const EXPIRED = 'Expired';
    /**
     * A cancelled subscription
     */
    public const CANCELLED = 'Cancelled';
    /**
     * When an error occurred during subscription
     */
    public const ERROR = 'Error';
}