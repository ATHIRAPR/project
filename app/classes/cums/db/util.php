<?php
namespace Cums\DB;

class Util
{
    public static function convert_type(array $target, array $keys, string $convert_func)
    {
        foreach ($keys as $key)
        {
            $target[$key] = $convert_func($target[$key]);
        }
        return $target;
    }

    public static function convert_type_list($target, array $keys, string $convert_func)
    {
        $result = [];
        foreach ($target as $t)
        {
            $result[] = self::convert_type($t, $keys, $convert_func);
        }
        return $result;
    }
}
