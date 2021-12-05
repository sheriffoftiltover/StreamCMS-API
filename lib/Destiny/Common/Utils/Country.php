<?php

namespace Destiny\Common\Utils;

use Destiny\Common\Application;
use Destiny\Common\Config;

/**
 * This class is weird
 */
abstract class Country
{

    /**
     * List of countries e.g.
     * [{"name":"Afghanistan","alpha-2":"AF","country-code":"004"},...] countries
     *
     * @var array
     */
    public static $countries = [];

    /**
     * List of countries by code e.g.
     * {"AF":"Afghanistan"} countries
     *
     * @var array
     */
    public static $codeIndex = null;

    public static function getCountryByCode($code)
    {
        $code = strtolower($code);
        $countries = self::getCountries();
        return (isset (self::$codeIndex [$code])) ? $countries [self::$codeIndex [$code]] : null;
    }

    /**
     * Return a cached list of countries
     *
     * @return array
     */
    public static function getCountries()
    {
        if (self::$countries == null) {
            $cacheDriver = Application::instance()->getCacheDriver();
            $countries = $cacheDriver->fetch('geodata');
            if (empty ($countries)) {
                $countries = json_decode(file_get_contents(Config::$a ['geodata'] ['json']), true);
                $cacheDriver->save('geodata', $countries);
            }
            if (is_array($countries)) {
                self::$countries = $countries;
            }
        }
        if (empty (self::$codeIndex)) {
            self::buildIndex();
        }
        return self::$countries;
    }

    private static function buildIndex()
    {
        foreach (self::$countries as $i => $country) {
            self::$codeIndex [strtolower($country ['alpha-2'])] = $i;
        }
    }

}