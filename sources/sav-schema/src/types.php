<?php
namespace SavSchema;

/**
 * 字符串解析
 * bool类型 加引号
 * number类型 加引号, 科学计数转换为值
 */
function stringVal($val)
{
    if (is_numeric($val)) {
        return strval($val);
    } elseif (is_bool($val)) {
        return $val ? "true" : "false";
    }
    return $val;
}

/**
 * 布尔解析
 * 字符串类型 只处理true/false/on/off 不区分大小写
 * number类型 0 => false, 其他均为 true
 */
function booleanVal($val)
{
    if (is_numeric($val)) {
        return boolval($val);
    } elseif (is_string($val)) {
        if (strnatcasecmp($val, "true") === 0) {
            return true;
        }
        if (strnatcasecmp($val, "false") === 0) {
            return false;
        }
        if (strnatcasecmp($val, "on") === 0) {
            return true;
        }
        if (strnatcasecmp($val, "off") === 0) {
            return false;
        }
    }
    return $val;
}

/**
 * 数字解析
 * 布尔类型
 * 字符串类型 只处理true/false/on/off 不区分大小写
 * 数字类型字符串 "1.23456792E8" => (double)123456792, 科学计数转换为值
 */
function numberVal($val)
{
    if (is_bool($val)) {
        return intval($val);
    } elseif (is_numeric($val)) {
      // 1.23456792E8
        $fval = floatval($val); // (double)123456792
        $nval = intval($val);   // (integer)1
        if ($fval != $nval) {
            return $fval;
        }
        return $nval;
    } elseif (is_string($val)) {
        if (strnatcasecmp($val, "true") === 0) {
            return 1;
        }
        if (strnatcasecmp($val, "false") === 0) {
            return 0;
        }
        if (strnatcasecmp($val, "on") === 0) {
            return 1;
        }
        if (strnatcasecmp($val, "off") === 0) {
            return 0;
        }
    }
    return $val;
}

/**
 * 数组解析
 * JSON字符串
 */
function arrayVal($val)
{
    if (is_string($val)) {
        if ($val[0] === '[' && $val[strlen($val) - 1] === ']') {
            return json_decode($val, true);
        }
    }
    return $val;
}

/**
 * 对象解析
 * JSON字符串
 */
function objectVal($val)
{
    if (is_string($val)) {
        if ($val[0] === '{' && $val[strlen($val) - 1] === '}') {
            return json_decode($val, true);
        }
    }
    return $val;
}

function isNatural($val)
{
    return is_numeric($val) && intval($val) == $val;
}

function checkIsAssocArray($val)
{
    $index = 0;
    foreach (array_keys($val) as $key) {
        if (!is_numeric($key) || ($key != $index++)) {
            return true;
        }
    }
    return false;
}

function isObjectArray($val)
{
    if (is_array($val)) {
        if (count($val) === 0) {
            return true;
        }
        return checkIsAssocArray($val);
    }
    return false;
}

function isObject($val)
{
    return is_object($val) || (is_array($val) && checkIsAssocArray($val));
}

function isArray($val)
{
    return is_array($val) && !checkIsAssocArray($val);
}

function registerTypes($schema)
{
    $types = array(
    array(
      "name" => "String",
      "check" => "is_string",
      "parse" => "SavSchema\\stringVal",
      "default" => ""
    ),
    array(
      "name" => "Number",
      "check" => "is_numeric",
      "parse" => "SavSchema\\numberVal",
      "default" => 0
    ),
    array(
      "name" => "Boolean",
      "check" => "is_bool",
      "parse" => "SavSchema\\booleanVal",
      "default" => false
    ),
    array(
      "name" => "Array",
      "check" => "SavSchema\\isArray",
      "parse" => "SavSchema\\arrayVal",
      "default" => function () {
        return [];
      }
    ),
    array(
      "name" => "Object",
      "check" => "SavSchema\\isObject",
      "parse" => "SavSchema\\objectVal",
      "default" => function () {
        return new \stdClass;
      }
    )
    );

    $rangs = array(
    "Int8" => array(-128, 127),
    "UInt8" => array(0, 255),
    "Byte" => array(-128, 255),
    "Int16" => array(-32768, 32767),
    "UInt16" => array(0, 65535),
    "Short" => array(-32768, 65535),
    "Int32" => array(-2147483648, 2147483647),
    "UInt32" => array(0, 4294967295),
    "Integer" => array(-2147483648, 4294967295),
    "Long" => array(-9007199254740991, 9007199254740991)
    );

    foreach ($rangs as $key => &$value) {
        array_push($types, array(
        "name" => $key,
        "default" => 0,
        "check" => function ($val) use ($value) {
            return isNatural($val) && $val >= $value[0] && $val <= $value[1];
        },
        "parse" => "SavSchema\\numberVal",
        "min" => $value[0],
        "max" => $value[1]
        ));
    }
    foreach ($types as $value) {
        $schema->registerType($value);
    }
};
