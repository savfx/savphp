<?php
use SavSchema\Schema;

describe("SchemaCheck", function () {

    it("SchemaCheck.rules", function () {
        $schema = new Schema();
        $User = $schema->build([
        "props" => [
        "name" => [
          "type" => "String",
          "optional" => true,
          "checks" => [
            ["lgt", 5],
            ["llte", 10],
            ["llt", 8],
          ],
        ],
        "age" => [
          "type" => "Number",
          "optional" => true,
          "checks" => [
            [">", 0],
            [">=", -10],
            ["<=", 20],
            ["<", 10],
          ],
        ],
        "sex" => [
          "type" => "String",
          "optional" => true,
          "checks" => [
            ["in", "male", "female", 1],
            ["nin", "men", "woman", 1]
          ]
        ],
        "nick" => [
          "type" => "String",
          "optional" => true,
          "checks" => [
            ["re", "/\w+/"],
            ["nre", "/\d+/"]
          ]
        ],
        "spec"  => [
          "type" => "String",
          "optional" => true,
          "checks" => [
            ["lgte", 5],
          ]
        ],
        ]
        ]);

        expect($User)->toBeA("object");

        expect(function () use ($User) {
            $User->check(["age" => 1]);
        })->not->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["age" => -20]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe(">");
            }
        })->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["age" => 2]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe(">=");
            }
        })->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["age" => 11]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe("<");
            }
        })->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["age" => 30]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe("<=");
            }
        })->toThrow();

        expect(function () use ($User) {
            $User->check(["name" => "jetiny"]);
        })->not->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["name" => "ab"]);
            } catch (\Exception $exp) {
                throw $exp;
            } finally {
                expect($exp->rule)->toBe("lgt");
            }
        })->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["name" => "123456789"]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe("llt");
            }
        })->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["name" => "1234567890a"]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe("llte");
            }
        })->toThrow();

        expect(function () use ($User) {
            $User->check(["sex" => "male"]);
        })->not->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["sex" => 1]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe("nin");
            }
        })->toThrow();

        expect(function () use ($User) {
            $User->check(["nick" => "male"]);
        })->not->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["nick" => "123"]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe("nre");
            }
        })->toThrow();

        expect(function () use ($User) {
            try {
                $User->check(["spec" => "1234"]);
            } catch (\Exception $err) {
                throw $err;
            } finally {
                expect($err->rule)->toBe("lgte");
            }
        })->toThrow();

    });
});
