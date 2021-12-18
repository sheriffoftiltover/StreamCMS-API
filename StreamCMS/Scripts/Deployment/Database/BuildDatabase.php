<?php

declare(strict_types=1);

require __DIR__ . '/../../../StreamCMSInit.php';

use StreamCMS\Utility\CLI\Commands\GenerateDatabase;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new GenerateDatabase());
$application->run();
