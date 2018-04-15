<?php

use SavUtil\CaseConvert;

describe("CaseConvert", function () {
    it('CaseConvert.basic', function () {
        $types = array(
        "pascal" => "HelloWorld",
        "camel" => "helloWorld",
        "hyphen" => "hello-world",
        "snake" => "hello_world",
        );
        foreach ($types as $str) {
            foreach ($types as $key => $value) {
                expect(CaseConvert::convert($key, $str))->toEqual($value);
            }
        }
    });
});
