<?php

declare(strict_types=1);

namespace Views;

use Laminas\Diactoros\Response;

abstract class AbstractView
{
    /**
     * Default view response code will be 200.
     * @return int
     */
    public function getResponseCode(): int
    {
        return 200;
    }

    abstract public function getResponse(): Response;
}
