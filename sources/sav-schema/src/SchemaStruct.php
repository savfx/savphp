<?php
namespace SavSchema;
use SavSchema\SchemaControl;

class SchemaStruct extends SchemaControl
{
  public function create($obj = NULL) {
    $isObj = isObjectArray($obj);
    $struct = $isObj ? $obj : [];
    foreach ($this->fields as $it) {
      $struct[$it->name] = ($isObj && isset($obj[$it->name])) ? $it->create($obj[$it->name]) : $it->create();
    }
    return $struct;
  }
  public function validate(&$obj, $opts) {
    try {
      $extract = isset($opts["extract"]) ? $opts["extract"] : NULL;
      $replace = isset($opts["replace"]) ? $opts["replace"] : NULL;
      $ret = $extract ? [] : $obj;
      foreach ($this->fields as $field) {
        try {
          $val = $field->validate($obj, $opts);
          // if (!isUndefined(val)) { php没有 undefined
          if ($replace) {
            $obj[$field->name] = $val;
          }
          if ($extract) {
            $ret[$field->name] = $val;
          }
        } catch (\Exception $err) {
          if (!isset($err->keys)) {
            $err->keys = [];
          }
          array_push($err->keys, $field->name);
          throw $err;
        }
      }
      return $ret;
    } catch (\Exception $err) {
      if (isset($err->keys)) {
        $err->path = implode('.', $err->keys);
      }
      throw $err;
    }
  }
}
