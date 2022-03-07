<?php

declare(strict_types=1);

namespace StreamCMS\API\Abstractions;

use Laminas\Diactoros\Response;
use StreamCMS\API\Abstractions\Interfaces\HasBodyInterface;
use StreamCMS\API\StreamCMSRequest;
use StreamCMS\API\Views\AbstractView;

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
            if ($this instanceof HasBodyInterface) {
                $this->parseRequest();
                $this->validateRequest();
            }
            return $this?->run()->getResponse() ?? new Response(null, 204);
        };
    }

    abstract public function run(): AbstractView|null;

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
