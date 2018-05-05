<?php

namespace SavRouter;

class Route
{
    public static function parse($path, $options = array())
    {
        $sensitive = array_key_exists('sensitive', $options) ? $options['sensitive'] : false;
        $end = array_key_exists('end', $options) ? $options['end'] : false;
        $strict = array_key_exists('strict', $options) ? $options['strict'] : false;
        $tokens = self::parseToken($path);
        $route = '';
        $keys = array();
        foreach ($tokens as $index => $token) {
            if (array_key_exists('text', $token)) {
                $route .= self::escapeString($token['text']);
            } else {
                if ($token['prefix']) {
                    if ($token['optional']) {
                        $route .= '(?:\/)?(?:([^\/]+?))?';
                    } else {
                        $route .= '(?:\/)(?:([^\/]+?))';
                    }
                } else {
                    $route .= '(?:([^\/]+?))';
                    if ($token['optional']) {
                        $route .= '?';
                    }
                }
                array_push($keys, $token['name']);
            }
        }
        if (!$strict) {
            $route .= '\\/?';
        }
        $route .= $end ? '$' : '(?=\/|$)';
        return array(
            "tokens" => $tokens,
            "keys" => $keys,
            "regexp" => '`^' . $route . '`' . ($sensitive ? '' : 'i'),
        );
    }
    public static function match($route, $path)
    {
        if (preg_match_all($route['regexp'], $path, $matches)) {
            $params = array();
            if (!((!array_key_exists('keys', $route)) ||
                (count($matches) == 0) ||
                (count($route['keys']) == 0)
            )) {
                $keys = $route['keys'];
                $len = count($keys);
                foreach ($matches as $index => $match) {
                    if ($index < $len) {
                        $params[$keys[$index]] = $match[0];
                    }
                }
            }
            return $params;
        }
    }
    public static function parseToken($path)
    {
        static $RE_TOKEN = '((\/)?:(\w+)(\?)?)';
        $parts = array();
        $pos = 0;
        $totle = strlen($path);
        while (preg_match($RE_TOKEN, $path, $mat, PREG_OFFSET_CAPTURE, $pos)) {
            // 依据可选时的匹配个数
            $optional = count($mat) === 4;
            // 全量 /:a => ['/:a', offset] , :a => [':a', offset]
            $whole = $mat[0];
            $len = strlen($whole[0]);
            $offset = $whole[1];
            // 是否在/后面 /:a => ['/', offset] , :a => ['', offset]
            $prefix = $mat[1][0];
            if ($pos < $offset) {
                array_push($parts, array(
                    "text" => substr($path, $pos, $offset - $pos),
                ));
            }
            // 名称 /:a => ['a', offset] , :a => ['a', offset]
            $name = $mat[2][0];
            array_push($parts, array(
                "name" => $name,
                "optional" => $optional,
                "prefix" => $prefix === '/' ? '/' : '',
            ));
            $pos = $offset + $len;
        }
        if ($pos < $totle) {
            array_push($parts, array(
                "text" => substr($path, $pos, $totle - $pos),
            ));
        }
        return $parts;
    }
    public static function escapeString($str)
    {
        return preg_replace('`([.+*?=^!:${}()[\]|\/])`', '$1', $str);
    }
    public static function complie($path, $options = array())
    {
        $encode = 'rawurlencode';
        if (array_key_exists('encode', $options)) {
            $encode = $options['encode'];
        }
        $tokens = is_array($path) ? $path : self::parseToken($path);
        $strVal = function ($val) use (&$encode) {
            if (is_bool($val)) {
                return $val ? 'true' : 'false';
            }
            return $encode ? $encode($val) : $val;
        };
        return function ($params = array()) use (&$tokens, $strVal) {
            $path = '';
            foreach ($tokens as $token) {
                if (array_key_exists('text', $token)) {
                    $path .= $token['text'];
                    continue;
                }
                $exists = array_key_exists($token['name'], $params);
                if (!$exists) {
                    if ($token['optional']) {
                        continue;
                    }
                }
                $val = $strVal($params[$token['name']]);
                $val = $token['prefix'] . $val;
                $path .= $val;
            }
            return $path;
        };
    }
}
