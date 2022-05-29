<?php

declare(strict_types=1);

namespace StreamCMS\Core\Utility\Files;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class FileSystem
{
    private static array $modelDirectories;

    /**
     * Returns an array of files that exist under a given path, with a php extension
     */
    public static function getPHPFilesInPath(string $path): array
    {
        return self::getFilesInPath($path, '.php');
    }

    public static function getFilesInPath(string $path, string $extension): array
    {
        $directorIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $files = [];
        foreach ($directorIterator as $filename => $pathObject) {
            if (str_ends_with($filename, $extension)) {
                /** @noinspection OffsetOperationsInspection */
                $files[$filename] = $filename;
            }
        }
        return $files;
    }

    public static function getFilesInPathRecursive(string $path, string $extension): array
    {
        $directorIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $files = [];
        foreach ($directorIterator as $filename => $pathObject) {
            // If it's a directory, recurse
            if (is_dir($filename) && ! str_ends_with($filename, '.')) {
                $files = \array_merge($files, self::getFilesInPathRecursive($path, $extension));
            } elseif (str_ends_with($filename, $extension)) {
                /** @noinspection OffsetOperationsInspection */
                $files[$filename] = $filename;
            }
        }
        return $files;
    }

    /**
     * Returns an array of all model folders
     */
    public static function getModelFolders(): array
    {
        self::$modelDirectories = [];
        $phpFiles = self::getPHPFilesInPath(STREAM_CMS_DIR);
        $modelDir = \DIRECTORY_SEPARATOR . 'Models' . \DIRECTORY_SEPARATOR;
        foreach ($phpFiles as $phpFile) {
            if (str_contains($phpFile, $modelDir) && ! str_contains($phpFile, 'Utility')) {
                $path = substr($phpFile, 0, strpos($phpFile, $modelDir));
                $path .= $modelDir;
                $path = realpath($path);
                self::$modelDirectories[$path] = $path;
            }
        }
        return self::$modelDirectories;
    }
}