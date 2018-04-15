<?php
namespace SavUtil;

class DateTime
{
    /**
     * 获取本地时间
     * @return Integer ms时间戳
     */
    public static function localTime()
    {
        return floor(microtime(true) * 1000);
    }
    /**
     * 获取UTC时间戳
     * @return Integer ms时间戳
     */
    public static function utcTime()
    {
        return static::localTime() - date('Z');
    }
    /**
     * 本地时间转UTC
     * @param  Integer $local 本地时间戳
     * @return Integer UTC时间戳
     */
    public static function localToUtc($local)
    {
        return $local - date('Z');
    }
    /**
     * UTC转本地时间戳
     * @param  Integer $utc UTC时间戳
     * @return Integer      本地时间戳
     */
    public static function utcToLocal($utc)
    {
        return $utc + date('Z');
    }
}
