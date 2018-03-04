<?php

use SavSchema\SchemaError;

describe("SchemaError", function() {

  it("SchemaError.Errors", function() {
    SchemaError::SetErrors([
      "type" => "NOOP",
    ]);
    expect(SchemaError::GetErrors()["type"])->toBe("NOOP");
  });
  
  it("SchemaError.type", function() {
    $err = new SchemaError("type", ["String", false]);
    expect($err->type)->toBe("String");
    expect($err->value)->toBe(false);
  });
  
  it("SchemaError.require", function() {
    $err = new SchemaError("require", ["username"]);
    expect($err->field)->toBe("username");
  });
  
  it("SchemaError.empty", function() {
    $err = new SchemaError("empty", ["username"]);
    expect($err->field)->toBe("username");
  });
  
  it("SchemaError.check", function() {
    $err = new SchemaError("check", ["username", "gt"]);
    expect($err->field)->toBe("username");
    expect($err->rule)->toBe("gt");
  });
  
  it("SchemaError.eql", function() {
    $err = new SchemaError("eql", ["password", "confirm_password"]);
    expect($err->field)->toBe("password");
    expect($err->fieldEql)->toBe("confirm_password");
  });
  
  it("SchemaError.rule", function() {
    $err = new SchemaError("rule", ["gte"]);
    expect($err->rule)->toBe("gte");
  });
  
  it("SchemaError.regexp", function() {
    $err = new SchemaError("regexp", ["/b"]);
    expect($err->regexp)->toBe("/b");
  });
  
  it("SchemaError.custom", function() {
    $err = new SchemaError(NULL, NULL, "custom");
    expect($err->getMsg())->toBe("custom");

    $err->setMsg("msg");
    expect($err->getMsg())->toBe("msg");
  });
  
});
