<?php

require_once __DIR__ . "/../src/types.php";

function is_assoc($arr)
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}

describe("types", function () {
    it("stringVal", function () {
        expect(SavSchema\stringVal(1))->toBe("1");
        expect(SavSchema\stringVal(1.8521111111))->toBe("1.8521111111");
        expect(SavSchema\stringVal(1.23456792E8))->toBe("123456792");

        expect(SavSchema\stringVal(false))->toBe("false");
        expect(SavSchema\stringVal(true))->toBe("true");
    });

    it("booleanVal", function () {
        expect(SavSchema\booleanVal(0))->toBe(false);
        expect(SavSchema\booleanVal(1))->toBe(true);
        expect(SavSchema\booleanVal(1.8521111111))->toBe(true);

        expect(SavSchema\booleanVal("True"))->toBe(true);
        expect(SavSchema\booleanVal("On"))->toBe(true);
        expect(SavSchema\booleanVal("False"))->toBe(false);
        expect(SavSchema\booleanVal("Off"))->toBe(false);

        expect(SavSchema\booleanVal("x"))->toBe("x");
    });

    it("numberVal", function () {
        expect(SavSchema\numberVal(true))->toBe(1);
        expect(SavSchema\numberVal(false))->toBe(0);

        expect(SavSchema\numberVal("True"))->toBe(1);
        expect(SavSchema\numberVal("On"))->toBe(1);
        expect(SavSchema\numberVal("False"))->toBe(0);
        expect(SavSchema\numberVal("Off"))->toBe(0);

        expect(SavSchema\numberVal("12"))->toBe(12);
        expect(SavSchema\numberVal("0.12"))->toBe(0.12);
        expect(SavSchema\numberVal("3.1415926"))->toBe(3.1415926);
        expect(SavSchema\numberVal("1.23456792E8"))->toBe((double)123456792);
      // as it
        expect(SavSchema\numberVal("3x"))->toBe("3x");
        expect(SavSchema\numberVal([]))->toBe([]);
    });

    it("arrayVal", function () {
        expect(SavSchema\arrayVal("[1, 2]"))->toBe([1, 2]);

        expect(SavSchema\arrayVal("x"))->toBe("x");
    });

    it("objectVal", function () {
        expect(SavSchema\objectVal("{\"a\": 2}"))->toBe(["a" => 2]);

        expect(SavSchema\objectVal("x"))->toBe("x");
    });

    it("isNatural", function () {
        expect(SavSchema\isNatural("1"))->toBe(true);
        expect(SavSchema\isNatural("1.2"))->toBe(false);
    });

    it("isArray", function () {
        expect(SavSchema\isArray([]))->toBe(true);
        expect(SavSchema\isArray(json_decode("[]")))->toBe(true);
        expect(SavSchema\isArray(["a" => 1]))->toBe(false);
    });

    it("isObject", function () {
        expect(SavSchema\isObject(new stdClass))->toBe(true);
        expect(SavSchema\isObject(json_decode("{}")))->toBe(true);
        expect(SavSchema\isObject(["a" => 1]))->toBe(true);

        expect(SavSchema\isObject([]))->toBe(false);
    });
});
