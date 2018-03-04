<?php
namespace SavSchema;
use SavSchema\SchemaControl;

class SchemaList extends SchemaControl
{
  public function parse($val) {
    return arrayVal($val);
  }
  public function create($value = NULL) {
    $value = $this->parse($value);
    if (isArray($value)) {
      $ref = $this->ref;
      foreach ($value as $key => $val) {
        $value[$key] = $ref->create($val);
      }
      return $value;
    }
    return array();
  }
  public function validate(&$obj, $opts) {
    $ref = $this->ref;
    $name = $this->name;
    $extract = isset($opts["extract"]) ? $opts["extract"] : NULL;
    $replace = isset($opts["replace"]) ? $opts["replace"] : NULL;
    $val = $this->parse($obj);
    if (!isArray($val)) {
      throw new SchemaError("type", [isset($name) ? $name : 'list', $val]);
    }
    $ret = [];
    for ($i = 0, $l = count($val); $i < $l; ++$i) {
      try {
        $newIt = checkValue($val[$i], $opts, $ref);
        if ($extract) {
          array_push($ret, $newIt);
        }
        if ($replace) {
          $val[$i] = $newIt;
        }
      } catch (exception $err) {
        if (!isset($err->keys)) {
          $err->keys = [];
        }
        array_push($err->keys, $i);
        throw $err;
      }
    }
    return $extract ? $ret : $obj;
  }
}

function checkValue($val, $opts, $ref) {
  if (is_callable($ref->parse)) {
    $val = $ref->parse($val);
  }
  if (is_callable($ref->validate)) {
    return $ref->validate($val, $opts);
  }
  if (!$ref->check($val)) {
    throw new SchemaError("type", [$ref->name, $val]);
  }
  return $val;
}
