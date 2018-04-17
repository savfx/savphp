<?php
use Sav\Remote;

describe("Remote", function () {

    it("Remote.base", function () {
        $sav = new Sav(array(
            "schemaPath" => __DIR__ . '/fixtures/schemas/',
        ));
        $sav->load(array(
            "modals" => array(
                array("name" => "Account")
            ),
            "actions" => array(
                array("name" => "login", "modal" => "Account",
                    "request" => "ReqAccountLogin", "response" => "ResAccountLogin")
            ),
        ));
        $ret = $sav->fetch("AccountLogin", array(
            "username" => "jetiny",
            "password" => "jetiny",
        ));
        expect($ret)->toBeA('string');
        expect($ret)->toEqual(json_encode(array(
            "id" => 123,
            "welcome" => "welcome jetiny"
        )));
    });
});
