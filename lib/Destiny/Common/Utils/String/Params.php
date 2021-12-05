<?php

namespace Destiny\Common\Utils\String;

use Destiny\Common\Exception;

abstract class Params
{

    public static function params($params, $join = '&', $wrap = '')
    {
        $str = [];
        foreach ($params as $n => $v) {
            $str [] = "$n=$wrap" . "$v" . $wrap;
        }
        return join($join, $str);
    }

    public static function search($pattern, $string)
    {
        if (self::match($pattern, $string)) {
            $keys = self::getKeys($pattern);
            $values = self::getValues($pattern, $string);
            if (count($values) != count($keys)) {
                throw new Exception ('$values and $keys must have the exact length');
            }
            $params = [];
            for ($i = 0; $i < count($keys); ++$i) {
                $key = self::getKey($keys [$i]);
                $params [self::getKeyName($key)] = self::getKeyValue($key, $values [$i]);
            }
            return $params;
        }
        return null;
    }

    public static function match($pattern, $string)
    {
        return (preg_match(self::getSearchString($pattern), $string) > 0);
    }

    protected static function getSearchString($pattern)
    {
        $find = ['/{[^}]*}/'];
        $replace = ['([A-z0-9\_\-\|\.]+)'];
        $subject = str_replace(['/', '.'], ['\\/', '\\.'], $pattern);
        return '/^' . preg_replace($find, $replace, $subject) . '$/i';
    }

    protected static function getKeys($pattern)
    {
        preg_match_all('/{[^}]*}/', $pattern, $keys, PREG_PATTERN_ORDER);
        if (is_array($keys [0])) {
            $keys = $keys [0];
        }
        return $keys;
    }

    protected static function getValues($pattern, $string)
    {
        preg_match_all(self::getSearchString($pattern), $string, $values, PREG_SET_ORDER);
        if (is_array($values [0])) {
            array_shift($values [0]);
            $values = $values [0];
        }
        return $values;
    }

    protected static function getKey($key)
    {
        return substr($key, 1, strlen($key) - 2);
    }

    protected static function getKeyName($key)
    {
        $pos = strpos($key, ':');
        return ($pos !== false && $pos > 0) ? substr($key, $pos + 1) : $key;
    }

    protected static function getKeyValue($key, $value, array $params = null)
    {
        $value = match (self::getKeyType($key)) {
            'int' => self::getValueAsInt($key, $value),
            default => self::getValueAsString($key, $value),
        };
        return $value;
    }

    protected static function getKeyType($key)
    {
        $pos = strpos($key, ':');
        return ($pos !== false && $pos > 0) ? substr($key, 0, $pos) : null;
    }

    protected static function getValueAsInt($key, $value)
    {
        return intval($value);
    }

    protected static function getValueAsString($key, $value)
    {
        return "$value";
    }

    public static function apply($pattern, array $params, $addSlashes = false)
    {
        $keys = self::getKeys($pattern);
        for ($i = 0, $rK = [], $rV = []; $i < count($keys); ++$i) {
            $key = self::getKey($keys [$i]);
            $keyName = self::getKeyName($key);
            $keyValue = self::getKeyValue($key, ((isset ($params [$keyName])) ? $params [$keyName] : $keyName), $params);
            $rK [] = $keys [$i];
            $rV [] = ($addSlashes) ? self::addSlashes($keyValue) : $keyValue;
        }
        return str_replace($rK, $rV, $pattern);
    }

    protected static function addSlashes($value)
    {
        if (!get_magic_quotes_gpc()) {
            $value = addslashes($value);
        }
        return $value;
    }

}