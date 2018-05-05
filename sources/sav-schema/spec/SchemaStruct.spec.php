<?php

use SavSchema\Schema;

describe("SchemaStruct", function () {
    $schema = new Schema();
    it("SchemaStruct.basic", function () use (&$schema) {
        $schema->build([
        "name" => "Sex",
        "enums" => [
        ["key" => "male", "value" => 1],
        ["key" => "female", "value" => 2],
        ]
        ]);
        $schema->build([
        "name" => "User",
        "props" => [
        "name" => "String",
        "age" => "Number",
        "sex" => "Sex"
        ],
        ]);
        expect($schema->User)->toBeA('object');
        expect($schema->User->create())->toBe(["name" => "", "age" => 0, "sex" => 1]);
        expect($schema->User->create(["name" => "a"]))->toBe(["name" => "a", "age" => 0, "sex" => 1]);
        expect($schema->User->create(["name" => "a", "some" => 2]))->toEqual(["name" => "a", "age" => 0, "sex" => 1, "some" => 2]);
        expect(function () use ($schema) {
            $schema->User->check(["name" => "a", "age" => 0, "sex" => 1]);
            $obj = ["name" => "a", "age" => "0", "sex" => 1];
            $schema->User->check($obj);
            expect($obj)->toBe(["name" => "a", "age" => "0", "sex" => 1]);
            expect($schema->User->extract($obj))->toBe(["name" => "a", "age" => 0, "sex" => 1]);
            expect($obj)->toBe(["name" => "a", "age" => "0", "sex" => 1]);
            $res = $schema->User->checkInPlace($obj);
            expect($res)->toBe(["name" => "a", "age" => "0", "sex" => 1]);
            expect($obj)->toBe(["name" => "a", "age" => 0, "sex" => 1]);
        })->not->toThrow();

        expect(function () use ($schema) {
            $schema->User->check(["name" => "a", "sex" => 1]);
        })->toThrow();

        expect(function () use ($schema) {
            $schema->User->extract(["sex" => 1]);
        })->toThrow();

    });

    it("SchemaStruct.fieldOptions", function () use (&$schema) {
        $schema->build([
        "name" => "User",
        "props" => [
        "name" => [
          "type" => "String",
          "optional" => true,
          "empty" => true,
          "nullable" => true,
        ]
        ],
        ]);
        expect($schema->User)->toBeA('object');
        expect($schema->User->create())->toBe(["name" => ""]);
        expect(function () use ($schema) {
            $schema->User->check([]);
            $schema->User->check(["name" => "jetiny"]);
            $schema->User->check(["name" => ""]);
            $schema->User->check(["name" => null]);
        })->not->toThrow();
    });

    it("SchemaStruct.fieldOption.eql", function () use (&$schema) {
        $schema->build([
        "name" => "User",
        "props" => [
        "password" => "String",
        "confirm_password" => [
          "type" => "String",
          "eql" => "password",
        ],
        ],
        ]);
        expect($schema->User)->toBeA('object');
        expect(function () use ($schema) {
            $schema->User->check(["password" => "1", "confirm_password" => "1"]);
        })->not->toThrow();
        expect(function () use ($schema) {
            $schema->User->check(["password" => 2, "confirm_password" => 1]);
        })->toThrow();
    });

});
