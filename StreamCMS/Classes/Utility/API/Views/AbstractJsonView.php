<?php

declare(strict_types=1);

namespace StreamCMS\Utility\API\Views;

use Laminas\Diactoros\Response\JsonResponse;

abstract class AbstractJsonView extends AbstractView
{
    /**
     * Converts the view object into an array for output from the API as JSON.
     * @return array
     */
    abstract public function toArray(): array;

    public function getResponse(): JsonResponse
    {
        return new JsonResponse($this->toArray(), $this->getResponseCode());
    }
}
