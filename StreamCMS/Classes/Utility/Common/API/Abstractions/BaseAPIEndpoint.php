<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Abstractions;

use Laminas\Diactoros\Response;
use StreamCMS\Utility\Common\API\StreamCMSRequest;

abstract class BaseAPIEndpoint
{
    protected StreamCMSRequest $request;
    protected array $vars;
    protected string $path;

    public function handleRequest(): callable
    {
        return function(StreamCMSRequest $request, array $vars, string $path): Response {
            $this->vars = $vars;
            $this->request = $request;
            return $this->run() ?? new Response(null, 204);
        };
    }

    abstract public function run(): Response|null;

    abstract public function getPath(): string;

    abstract public function getMethod(): string;

    public static function getMap(): array
    {
        $instance = new static();
        return [
            $instance->getMethod(),
            $instance->getPath(),
            $instance->handleRequest()
        ];
    }
}
