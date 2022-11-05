<?php

declare(strict_types=1);

namespace StreamCMS\Framework\API\Endpoints;

use Laminas\Diactoros\Response;
use StreamCMS\Core\API\Abstractions\Interfaces\HasBodyInterface;
use StreamCMS\Core\API\StreamCMSRequest;
use StreamCMS\Site\Models\Site;
use Views\AbstractView;

abstract class AbstractAPIEndpoint
{
    protected StreamCMSRequest $request;
    protected array $vars;
    protected string $path;
    protected Site|null $site;

    // receiveRequest

    // parseRequest

    // validateRequest

    // authenticateRequest

    // authorizeRequest

    // constructResponse

    // returnResponse

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
