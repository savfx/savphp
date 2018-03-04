<?php

use SavSchema\Schema;

describe("Schema", function() {
  $schema = new Schema();
  it("Schema.registerType", function() use(&$schema){
    $schema->registerType([
      "name" => "NewType"
    ]);
    expect($schema->NewType)->toBeA('object');
  });
});
