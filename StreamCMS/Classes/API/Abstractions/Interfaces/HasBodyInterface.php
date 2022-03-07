<?php

declare(strict_types=1);

namespace StreamCMS\API\Abstractions\Interfaces;

interface HasBodyInterface
{
    public function parseRequest(): void;

    public function validateRequest(): void;
}
