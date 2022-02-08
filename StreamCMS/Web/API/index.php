<?php

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Router;
use StreamCMS\Utility\Common\API\Abstractions\Router\StreamCMSRouter;
use StreamCMS\Utility\Common\API\Middleware\ContextMiddleware\IdentityContextMiddleware;
use StreamCMS\Utility\Common\API\Middleware\ContextMiddleware\SiteContextMiddleware;
use StreamCMS\Utility\Common\API\Routes\StreamCMSRoutes;
use StreamCMS\Utility\Common\API\Strategies\APIStrategy;
use StreamCMS\Utility\Common\API\StreamCMSRequest;

$dirPath = __DIR__;

define('STREAM_CMS_INIT_PATH', realpath("{$dirPath}/../../StreamCMS/StreamCMSInit.php"));

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require '../../StreamCMS/StreamCMSInit.php';

$request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
// Convert the request to a StreamCMS request.
$request = new StreamCMSRequest($request);
$responseFactory = new ResponseFactory();
$router = new StreamCMSRouter();
// Add our middleware
$router->middleware(new IdentityContextMiddleware());
$router->middleware(new SiteContextMiddleware());
$router->setStrategy(new APIStrategy($responseFactory));
// Add our routes.
new StreamCMSRoutes($router);

if ($request->getMethod() === 'OPTIONS') {
    $response = new Response('php://memory', 200, []);
} else {
    $response = $router->dispatch($request);
}

(new SapiEmitter())->emit($response);