<?php

declare(strict_types=1);

// Require the autoloader
use Symfony\Component\Dotenv\Dotenv;

// Define our base path.
const STREAM_CMS_DIR = __DIR__;

// Require our autoload
require STREAM_CMS_DIR . '/vendor/autoload.php';

// Load our environment file(s)
$dotEnv = new Dotenv();
$dotEnv->load(STREAM_CMS_DIR . '/.env');