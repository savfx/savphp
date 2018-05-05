<?php
namespace SavSchema;

use SavSchema\SchemaControl;

class SchemaRefer extends SchemaControl
{
    public function create($value = null)
    {
        return $this->ref->create($value);
    }
    public function validate(&$obj, $opts)
    {
        return $this->ref->validate($obj, $opts);
    }
}
