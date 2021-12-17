<?php

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Router;
use StreamCMS\Utility\Common\API\Middleware\IdentityContextMiddleware;
use StreamCMS\Utility\Common\API\Middleware\SiteContextMiddleware;
use StreamCMS\Utility\Common\API\Strategies\APIStrategy;
use StreamCMS\Utility\Common\API\StreamCMSRequest;

$dirPath = __DIR__;

define('STREAM_CMS_INIT_PATH', realpath("{$dirPath}/../StreamCMS/StreamCMSInit.php"));

require '../../StreamCMSInit.php';

$request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
// Convert the request to a StreamCMS request.
$request = new StreamCMSRequest($request);
$responseFactory = new ResponseFactory();
$router = new Router();
// Add our middleware
$router->middleware(new IdentityContextMiddleware());
$router->middleware(new SiteContextMiddleware());
$router->setStrategy(new APIStrategy($responseFactory));
if ($request->getMethod() === 'OPTIONS') {
    $response = new Response('php://memory', 200, []);
} else {
    $response = $router->dispatch($request);
}

(new SapiEmitter())->emit($response);