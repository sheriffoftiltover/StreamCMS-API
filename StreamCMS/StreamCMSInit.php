<?php

declare(strict_types=1);

// Require the autoloader
use Symfony\Component\Dotenv\Dotenv;

$dirPath = __DIR__;

require "{$dirPath}/vendor/autoload.php";

// Load our environment file(s)
$dotEnv = new Dotenv();
$dotEnv->load("{$dirPath}/.env");