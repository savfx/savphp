<?php
namespace SavUtil;

class CaseConvert
{
    public static function camelCase($str)
    {
        if (!is_string($str)) {
            return '';
        }
        return lcfirst(preg_replace_callback(static::$camelCaseRe, function ($mats) {
            if (isset($mats[1])) {
                return strtoupper($mats[1]);
            }
            return '';
        }, $str));
    }

    public static function hyphenCase($str)
    {
        return preg_replace_callback(static::$upperCaseRe, function ($mats) {
            return '-' . lcfirst($mats[0]);
        }, static::camelCase($str));
    }

    public static function snakeCase($str)
    {
        return preg_replace_callback(static::$upperCaseRe, function ($mats) {
            return '_' . lcfirst($mats[0]);
        }, static::camelCase($str));
    }

    public static function pascalCase($str)
    {
        return ucfirst(static::camelCase($str));
    }

    public static function convert($type, $str)
    {
        switch ($type) {
            case 'pascal':
                return static::pascalCase($str);
            case 'camel':
                return static::camelCase($str);
            case 'snake':
                return static::snakeCase($str);
            case 'hyphen':
                return static::hyphenCase($str);
        }
        return $str;
    }

    private static $camelCaseRe = '/[-_](\w)/';
    private static $upperCaseRe = '/([A-Z])/';
}
