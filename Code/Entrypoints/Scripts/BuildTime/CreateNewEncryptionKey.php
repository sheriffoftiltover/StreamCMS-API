<?php

declare(strict_types=1);

use Defuse\Crypto\Key;

require __DIR__ . '/../../StreamCMSInit.php';

if (!isset($_ENV['ENCRYPTION_KEY'])) {
    $contents = file_get_contents(STREAM_CMS_DIR . '/.env');

    $randomKey = Key::createNewRandomKey()->saveToAsciiSafeString();
    $contents .= "\nENCRYPTION_KEY=\"{$randomKey}\"";

    file_put_contents(STREAM_CMS_DIR . '/.env', $contents);
}