<?php

declare(strict_types=1);

namespace StreamCMS\Core\Classes;

use StreamCMS\Core\Utility\Files\FileSystem;

final class ClassUtils
{
    public static function getNamespaceFromPath(string $filePath): string
    {
        return str_replace([STREAM_CMS_DIR, 'Classes/', DIRECTORY_SEPARATOR, '.php'], ['StreamCMS', '', '\\', ''], $filePath);
    }

    public static function getClassesInDir(string $dirPath): array
    {
        $classes = [];
        $filePaths = FileSystem::getPHPFilesInPath($dirPath);
        foreach ($filePaths as $filePath) {
            $classes[] = self::getNamespaceFromPath($filePath);
        }
        return $classes;
    }
}
