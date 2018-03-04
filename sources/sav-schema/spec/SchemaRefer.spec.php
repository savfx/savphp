<?php

use SavSchema\Schema;

describe("SchemaRefer", function() {
  $schema = new Schema();
  it("SchemaRefer.StringList", function() use(&$schema){
    $schema->declare([
      "name" => "StringList",
      "list" => "String"
    ]);
    $ReferStringList = $schema->declare([
      "refer" => "StringList"
    ]);
    expect($ReferStringList)->toBeA("object");
    expect($ReferStringList->create())->toBe([]);
    expect($ReferStringList->create([1, "2"]))->toBe(["1", "2"]);
    expect($ReferStringList->create('["a", "b"]'))->toBe(['a', 'b']);
    expect(function () use($ReferStringList) {
      $ReferStringList->check([]);
      $arr = [1, "2"];
      $ReferStringList->check($arr);
      expect($arr)->toBe([1, "2"]);
      expect($ReferStringList->extract($arr))->toBe(["1", "2"]);
      expect($arr)->toBe([1, "2"]);
    })->not->toThrow();
  });
});
