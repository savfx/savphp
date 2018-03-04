<?php

namespace SavSchema;
use SavSchema\SchemaBase as SchemaBase;

class SchemaType extends SchemaBase
{
  public function parse($val) {
    return $this->opts["parse"]($val);
  }
  public function check($val) {
    return $this->opts["check"]($val);
  }
  public function create($val = NULL) {
    if (isset($val)) {
      return $this->parse($val);
    }
    $factory = $this->opts["default"];
    if (is_callable($factory)) {
      return $factory();
    }
    return $factory;
  }
}
