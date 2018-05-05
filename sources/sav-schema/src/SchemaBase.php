<?php

namespace SavSchema;

class SchemaBase
{
    public $schema;
    public function __construct($schema, $opts)
    {
        $this->opts = $opts;
        $this->schema = $schema;
    }
    function __get($_property)
    {
        if ($_property === 'ref') {
            return $this->schema->getRef($this);
        }
        if (isset($this->opts[$_property])) {
            return $this->opts[$_property];
        }
    }
}
