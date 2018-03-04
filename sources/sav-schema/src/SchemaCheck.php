<?php
namespace SavSchema;

class SchemaCheck
{
  public function __construct($schema, $opts) {
    $this->opts = $opts;
    $this->schema = $schema;
  }
  public function check($value, $args) {
    return $this->opts["check"]($value, $args);
  }
  function __get($_property) {
    if ($_property === 'name') {
      return $this->opts["name"];
    }
    if ($_property === 'alias') {
      return $this->opts["alias"];
    }
    if ($_property === 'argc') {
      return $this->opts["argc"] || 1;
    }
  }
}
