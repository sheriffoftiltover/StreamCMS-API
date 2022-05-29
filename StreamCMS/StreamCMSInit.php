<?php

declare(strict_types=1);

// Require the autoloader
use StreamCMS\Core\Logging\LogUtil;
use Symfony\Component\Dotenv\Dotenv;

// Define our base path.
const STREAM_CMS_DIR = __DIR__;

// Require our autoload
require STREAM_CMS_DIR . '/vendor/autoload.php';

LogUtil::init();

// Load our environment file(s)
$dotEnv = new Dotenv();
$dotEnv->load(STREAM_CMS_DIR . '/.env');
