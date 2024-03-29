<?php

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use StreamCMS\Core\API\Abstractions\Router\StreamCMSRouter;
use StreamCMS\Core\API\Middleware\ContextMiddleware\IdentityContextMiddleware;
use StreamCMS\Core\API\Middleware\ContextMiddleware\SiteContextMiddleware;
use StreamCMS\Core\API\ResponseFactories\ResponseFactory;
use StreamCMS\API\Routes\StreamCMSRoutes;
use StreamCMS\Core\API\Strategies\APIStrategy;
use StreamCMS\Core\API\StreamCMSRequest;

$dirPath = __DIR__;

define('STREAM_CMS_INIT_PATH', dirname('StreamCMS/StreamCMSInit.php', 2));

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require '../../StreamCMS/StreamCMSInit.php';

// Bro what the fuck..
$_POST = array_merge($_POST, json_decode(file_get_contents('php://input'), true) ?? []);

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
    $response = new Response('php://memory', 200, $responseFactory::getDefaultHeaders());
} else {
    $response = $router->dispatch($request);
}

(new SapiEmitter())->emit($response);
