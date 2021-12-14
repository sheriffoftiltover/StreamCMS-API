<?php

declare(strict_types=1);

use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;

$dirPath = __DIR__;

define('STREAM_CMS_INIT_PATH', realpath("{$dirPath}/../StreamCMS/StreamCMSInit.php"));

require '../../StreamCMSInit.php';

$request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$responseFactory = new ResponseFactory();
