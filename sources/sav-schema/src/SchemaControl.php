<?php

namespace SavSchema;

use SavSchema\SchemaBase;

abstract class SchemaControl extends SchemaBase
{
    public function check($obj, $opts = [])
    {
        return $this->validate($obj, $opts);
    }
    public function checkInPlace(&$obj, $opts = [])
    {
        $opts["replace"] = true;
        return $this->validate($obj, $opts);
    }
    public function extract($obj, $opts = [])
    {
        $opts["extract"] = true;
        return $this->validate($obj, $opts);
    }
    abstract protected function validate (&$obj, $opts);
}
