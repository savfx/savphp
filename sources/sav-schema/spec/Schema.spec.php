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
  
  it("Schema.load", function() use(&$schema){
    $json = json_decode(file_get_contents(__DIR__.'/fixtures/contract-min.json'), true);
    $nameCount = count(array_keys($schema->nameMap));
    $schema->load($json);
    expect($json)->toBeA('array');
    expect(count($json['fields']))->toBe(count(array_keys($schema->idMap)));
    expect( $nameCount + 
      count($json['enums']) + 
      count($json['lists']) + 
      count($json['structs']) + 
      count($json['schemas'])
      )->toBe(count(array_keys($schema->nameMap)));
  });
});
