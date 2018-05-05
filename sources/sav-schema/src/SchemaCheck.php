<?php
namespace SavSchema;

class SchemaCheck
{
    public function __construct($schema, $opts)
    {
        $this->opts = $opts;
        $this->schema = $schema;
    }
    public function check($value, $args)
    {
        return $this->opts["check"]($value, $args);
    }
    function __get($_property)
    {
        if (isset($this->opts[$_property])) {
            return $this->opts[$_property];
        }
    }
}
