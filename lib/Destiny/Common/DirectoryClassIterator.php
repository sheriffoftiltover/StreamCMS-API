<?php
namespace Destiny\Common;

use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use ReflectionException;
use RegexIterator;
use SplFileInfo;

/**
 * Reads all files in a folder and finds the .php ones with classes in them
 */
class DirectoryClassIterator implements Iterator {
    
    private $position = 0;
    
    /**
     * @var ReflectionClass[]
     */
    private $array = [];
    
    /**
     * @var string
     */
    private $base;
    
    /**
     * @var string
     */
    private $path;

    /**
     *
     * @param string $base
     * @param string $path
     * @throws ReflectionException
     */
    public function __construct($base, $path) {
        $this->base = $base;
        $this->path = $path;
        $this->array = $this->getClasses ();
        $this->position = 0;
    }

    /**
     * Ported from Doctrine class
     * Load all files in a folder
     *
     * @return ReflectionClass[]
     * @throws ReflectionException
     */
    private function getClasses(): array {
        $files = self::getFiles ();
        $classes = [];
        // Run through all the public classes, that have Action annotations, check for Route annotations
        foreach ( $files as $file ) {
            // PSR-0 format namespace / folder / filename
            // strip the base off, and treat the rest as the namespace path, with the .php removed
            $class = str_replace ( '/', '\\', substr ( $file->getPathname (), strlen ( $this->base ), - 4 ) );
            
            // No class found, no annotations
            if (! $class) continue;
            
            // Make sure the class is not abstract
            $refl = new ReflectionClass ( $class );
            if ($refl->isAbstract ()) continue;
            
            $classes [] = $refl;
        }
        return $classes;
    }

    /**
     * Get all the php files in a folder
     *
     * @return SplFileInfo[]
     */
    private function getFiles(): array {
        $directory = new RecursiveDirectoryIterator ( $this->base . $this->path );
        $iterator = new RecursiveIteratorIterator ( $directory, RecursiveIteratorIterator::SELF_FIRST );
        $regex = new RegexIterator ( $iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );
        $files = [];
        foreach ( $regex as $file ) {
            $filename = $file [0];
            $files [] = new SplFileInfo ( $filename );
        }
        return $files;
    }

    public function current() {
        return $this->array [$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function valid() {
        return isset ($this->array [$this->position]);
    }

}