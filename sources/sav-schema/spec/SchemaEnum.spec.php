<?php

use SavSchema\SchemaEnum;

describe("SchemaEnum", function () {
    $enum = new SchemaEnum(null, [
    "enums" => [
      ["key" => "male", "value" => 1],
      ["key" => "female", "value" => 2],
    ]
    ]);
    it("SchemaEnum.create", function () use ($enum) {
        expect($enum->create())->toBe(1);
        expect($enum->create(2))->toBe(2);
    });
    it("SchemaEnum.create.mixed", function () use ($enum) {
        expect($enum->create(false))->toBe(false);
    });
    it("SchemaEnum.check", function () use ($enum) {
        expect($enum->check(1))->toBe(true);
        expect($enum->check("female"))->toBe(true);

        expect($enum->check(false))->toBe(false);
        expect($enum->check(3))->toBe(false);
    });
    it("SchemaEnum.parse", function () use ($enum) {
        expect($enum->parse(1))->toBe(1);
        expect($enum->parse("female"))->toBe("female");
        expect($enum->parse("1"))->toBe(1);

        expect($enum->parse(3))->toBe(3);
        expect($enum->parse(null))->toBe(null);
        expect($enum->parse(false))->toBe(false);
    });

    it("SchemaEnum.getEnum", function () use ($enum) {
        expect($enum->key(1))->toBe("male");
        expect($enum->value("female"))->toBe(2);
    });
});
