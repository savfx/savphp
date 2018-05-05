<?php
namespace SavSchema;

function gt($value, $argv)
{
    return $value > $argv[1];
}

function gte($value, $argv)
{
    return $value >= $argv[1];
}

function lt($value, $argv)
{
    return $value < $argv[1];
}

function lte($value, $argv)
{
    return $value <= $argv[1];
}

function inArray($value, $argv)
{
    return array_search($value, $argv) > 0;
}

function nin($value, $argv)
{
    return array_search($value, $argv) <= 0;
}

function lgt($value, $argv)
{
    return gt(strlen($value), $argv);
}

function lgte($value, $argv)
{
    return gte(strlen($value), $argv);
}

function llt($value, $argv)
{
    return lt(strlen($value), $argv);
}

function llte($value, $argv)
{
    return lte(strlen($value), $argv);
}

function re($value, $argv)
{
    return preg_match($argv[1], $value);
}

function nre($value, $argv)
{
    return !re($value, $argv);
}

function registerChecks($schema)
{
    $checks = array(
    array("name" => 'gt', "alias" => '>', "check" => "SavSchema\\gt"),
    array("name" => 'gte', "alias" => '>=', "check" => "SavSchema\\gte"),
    array("name" => 'lt', "alias" => '<', "check" => "SavSchema\\lt"),
    array("name" => 'lte', "alias" => '<=', "check" => "SavSchema\\lte"),
    array("name" => 'in', "check" => "SavSchema\\inArray"),
    array("name" => 'nin', "check" => "SavSchema\\nin"),
    array("name" => 'lgt', "check" => "SavSchema\\lgt"),
    array("name" => 'lgte', "check" => "SavSchema\\lgte"),
    array("name" => 'llt', "check" => "SavSchema\\llt"),
    array("name" => 'llte', "check" => "SavSchema\\llte"),
    array("name" => 're', "check" => "SavSchema\\re"),
    array("name" => 'nre', "check" => "SavSchema\\nre")
    );
    foreach ($checks as $it) {
        $schema->registerCheck($it);
    }
}
