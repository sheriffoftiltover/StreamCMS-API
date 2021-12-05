<?php

namespace Destiny\Common;

abstract class Config
{

    /**
     * The configuration array
     *
     * @var array
     */
    public static $a = [];

    /**
     * Load the config stack
     *
     * @param array $array
     */
    public static function load(array $config)
    {
        self::$a = $config;
        // Set environment vars
        if (isset (self::$a ['env']) && !empty (self::$a ['env'])) {
            foreach (self::$a ['env'] as $i => $v) {
                ini_set($i, $v);
            }
        }
    }

    /**
     * Return the cdn domain/version
     *
     * @param string $protocol
     * @return string
     */
    public static function cdnv($protocol = '//')
    {
        return self::cdn($protocol) . '/' . Config::version();
    }

    /**
     * Return the cdn domain
     *
     * @param string $protocol
     * @return string
     */
    public static function cdn($protocol = '//')
    {
        $domain = self::$a ['cdn'] ['domain'];
        $port = (isset(self::$a ['cdn'] ['port'])) ? ':' . self::$a ['cdn'] ['port'] : '';
        return (!empty ($domain)) ? $protocol . $domain . $port : '';
    }

    /**
     * Return the application version
     *
     * @return string
     */
    public static function version()
    {
        return self::$a ['version'];
    }

    /**
     * @param double $v
     * @param string $protocol
     * @return string
     */
    public static function cdnvf($v, $protocol = '//')
    {
        return self::cdn($protocol) . '/' . $v;
    }

}