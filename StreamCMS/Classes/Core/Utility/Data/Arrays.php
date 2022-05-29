<?php

declare(strict_types=1);

namespace StreamCMS\Core\Utility\Data;

final class Arrays
{
    public static function camelCaseKeys(array $data): array
    {
        $newArray = [];
        foreach ($data as $key => &$value) {
            if (is_string($key)) {
                $key = str_replace('_', ' ', $key);
                $key = ucwords($key);
                $key = str_replace(' ', '', $key);
                $key = strtolower($key[0]) . substr($key, 1);
            }
            if (is_array($value)) {
                $value = self::camelCaseKeys($value);
            }

            $newArray[$key] = $value;
        }
        return $newArray;
    }
}
