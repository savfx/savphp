<?php

use SavSchema\SchemaType;
use SavSchema\Schema;

describe("SchemaType", function () {
    $schema = new Schema();
    it("SchemaType.String", function () use (&$schema) {
        expect($schema->String)->toBeTruthy();
        expect($schema->String->create())->toBe("");
        expect($schema->String->create(false))->toBe("false");
        expect($schema->String->create(1))->toBe("1");
        expect($schema->String->create([]))->toBe([]);

        expect($schema->String->parse(1))->toBe("1");
        expect($schema->String->parse("false"))->toBe("false");

        expect($schema->String->check("1"))->toBe(true);
        expect($schema->String->check(1))->toBe(false);
    });
    it("SchemaType.Number", function () use (&$schema) {
        expect($schema->Number)->toBeTruthy();
        expect($schema->Number->create())->toBe(0);
        expect($schema->Number->create(false))->toBe(0);
        expect($schema->Number->create(true))->toBe(1);
        expect($schema->Number->create("2"))->toBe(2);
        expect($schema->Number->create([]))->toBe([]);

        expect($schema->Number->parse("1"))->toBe(1);
        expect($schema->Number->parse(true))->toBe(1);

        expect($schema->Number->check(1))->toBe(true);
        expect($schema->Number->check(1.34))->toBe(true);
        expect($schema->Number->check(false))->toBe(false);
    });
    it("SchemaType.Boolean", function () use (&$schema) {
        expect($schema->Boolean)->toBeTruthy();
        expect($schema->Boolean->create())->toBe(false);
        expect($schema->Boolean->create("1"))->toBe(true);
        expect($schema->Boolean->create("True"))->toBe(true);
        expect($schema->Boolean->create(3.1))->toBe(true);

        expect($schema->Boolean->parse(true))->toBe(true);
        expect($schema->Boolean->parse(1))->toBe(true);

        expect($schema->Boolean->check(true))->toBe(true);
        expect($schema->Boolean->check("1"))->toBe(false);
    });

    it("SchemaType.Array", function () use (&$schema) {
        expect($schema->Array)->toBeTruthy();
        expect($schema->Array->create())->toBe([]);
        expect($schema->Array->create(["1"]))->toBe(["1"]);
        expect($schema->Array->create("[]"))->toBe([]);
        expect($schema->Array->create("[\"1\"]"))->toBe(["1"]);

        expect($schema->Array->parse([]))->toBe([]);
        expect($schema->Array->parse("[1]"))->toBe([1]);

        expect($schema->Array->check([]))->toBe(true);
        expect($schema->Array->check(["1"]))->toBe(true);

        expect($schema->Array->check(["a" => "b"]))->toBe(false);
        expect($schema->Array->check(new stdClass))->toBe(false);
    });

    it("SchemaType.Object", function () use (&$schema) {
        expect($schema->Object)->toBeTruthy();
        expect($schema->Object->create())->toBeA('object');
        expect($schema->Object->create(new stdClass))->toBeA('object');
        expect($schema->Object->create(["a" => 1]))->toBe(["a" => 1]);
        expect($schema->Object->create("{\"a\": 1}"))->toBe(["a" => 1]);

        expect($schema->Object->parse(["a" => 1]))->toBe(["a" => 1]);
        expect($schema->Object->parse(new stdClass))->toBeA('object');

        expect($schema->Object->check(["a" => 1]))->toBe(true);
        expect($schema->Object->check(json_decode("{\"a\": 1}")))->toBe(true);
        expect($schema->Object->check(json_decode("{\"a\": 1}", true)))->toBe(true);
        expect($schema->Object->check(new stdClass))->toBe(true);

        expect($schema->Object->check([]))->toBe(false);
        expect($schema->Object->check(["a", "b"]))->toBe(false);
    });

    it("SchemaType.Integer", function () use (&$schema) {
        expect($schema->Byte->check(1))->toBe(true);
        expect($schema->Byte->check(-255))->toBe(false);
        expect($schema->Byte->check(256))->toBe(false);

        expect($schema->Short->check(1))->toBe(true);
        expect($schema->Short->check(-65535))->toBe(false);
        expect($schema->Short->check(65537))->toBe(false);

        expect($schema->Integer->check(1))->toBe(true);
        expect($schema->Integer->check(-4294967295))->toBe(false);
        expect($schema->Integer->check(4294967296))->toBe(false);

        expect($schema->Long->check(1))->toBe(true);
        expect($schema->Long->check(-9007199254740992))->toBe(false);
        expect($schema->Long->check(9007199254740992))->toBe(false);

        expect($schema->Byte->check(1.2))->toBe(false);
    });
});
