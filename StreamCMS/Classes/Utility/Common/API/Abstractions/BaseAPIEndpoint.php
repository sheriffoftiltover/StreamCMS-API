<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Abstractions;

use StreamCMS\Utility\Common\API\StreamCMSRequest;

abstract class BaseAPIEndpoint
{
    public function __invoke(StreamCMSRequest $request)
    {
        $this->parse();
        return $this->run();
    }

    abstract public function parse(): void;

    abstract public function run();
}
