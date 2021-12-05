<?php
declare(strict_types=1);

namespace Destiny\Common;

use Destiny\Common\Utils\Http;

class Response
{

    private $headers = [];
    private $location;

    public function __construct(private $status = Http::STATUS_OK, private $body = null)
    {
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function addHeader($name, $value)
    {
        $this->headers [] = [$name, $value];
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

}