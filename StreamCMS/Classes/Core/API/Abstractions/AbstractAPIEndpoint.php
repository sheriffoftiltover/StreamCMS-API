<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\Abstractions;

use Laminas\Diactoros\Response;
use StreamCMS\Core\API\Abstractions\Interfaces\HasBodyInterface;
use StreamCMS\Core\API\StreamCMSRequest;
use StreamCMS\Core\API\Views\AbstractView;
use StreamCMS\Site\Models\Site;

abstract class AbstractAPIEndpoint
{
    protected StreamCMSRequest $request;
    protected array $vars;
    protected string $path;
    protected Site|null $site;

    public function handleRequest(): callable
    {
        return function(StreamCMSRequest $request, array $vars, string $path): Response {
            $this->vars = $vars;
            $this->request = $request;
            $this->site = $this->request->getSiteContext()->getSite();
            if ($this instanceof HasBodyInterface) {
                $this->parseRequest();
                $this->validateRequest();
            }
            return $this->run()?->getResponse() ?? new Response(null, 204);
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
            $instance->handleRequest(),
        ];
    }
}
