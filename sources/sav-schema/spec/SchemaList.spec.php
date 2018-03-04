<?php

use SavSchema\Schema;

describe("SchemaList", function() {
  $schema = new Schema();
  it("SchemaList.StringList", function() use(&$schema){
    $schema->declare([
      "name" => "StringList",
      "list" => "String"
    ]);
    expect($schema->StringList)->toBeA("object");
    expect($schema->StringList->create())->toBe([]);
    expect($schema->StringList->create([1, "2"]))->toBe(["1", "2"]);
    expect($schema->StringList->create('["a", "b"]'))->toBe(['a', 'b']);
    expect(function () use($schema) {
      $schema->StringList->check([]);
      $arr = [1, "2"];
      $schema->StringList->check($arr);
      expect($arr)->toBe([1, "2"]);
      expect($schema->StringList->extract($arr))->toBe(["1", "2"]);
      expect($arr)->toBe([1, "2"]);
    })->not->toThrow();
  });
});
