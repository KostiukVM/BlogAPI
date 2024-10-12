<?php

namespace App\Traits;

trait CamelCaseJsonTrait
{
    public static function convertArrayKeysToCamelCase(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $result[$camelKey] = is_array($value) ? self::convertArrayKeysToCamelCase($value) : $value;
        }

        return $result;
    }
}
