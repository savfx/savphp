<?php
use Sav\Sav;
use Sav\Spec\Plugin;

describe("Sav", function () {

    it("Sav.base", function () {
        $sav = new Sav(array(
            "schemaPath" => __DIR__ . '/fixtures/schemas/',
            "modalPath" => __DIR__ . '/fixtures/modals/',
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
        $mat = $sav->execute("/account/login", "POST", array(
            "username" => "jetiny",
            "password" => "jetiny",
        ), true);
        expect($mat)->toBeA('string');
        expect($mat)->toEqual(json_encode(array(
            "id" => 123,
            "welcome" => "welcome jetiny"
        )));
    });

    it("Sav.prop", function () {
        $sav = new Sav();
        $sav->prop("db", function ($ctx) {
            return "db";
        });
        $sav->prop("test", "test");
        $ctx = $sav->prepare("/", "GET", array());
        expect($ctx->db)->toEqual("db");
        expect($ctx->test)->toEqual("test");
    });

    it("Sav.use", function () {
        $sav = new Sav();
        $sav->use(function ($sav, $ctx) {
            expect($ctx->db)->toEqual(null);
            expect($ctx->test)->toEqual(null);
            $sav->prop("db", function () {
                return "db";
            });
            $sav->prop("test", "test");
            expect($ctx->test)->toEqual("test");
            $sav->prop("test", "test2");
            expect($ctx->test)->toEqual("test");
            $sav->prop("test", null);
        });
        $ctx = $sav->prepare("/", "GET", array());
        expect($ctx->db)->toEqual("db");
        expect($ctx->test)->toEqual("test");
    });

    it("Sav.use.static", function () {
        spl_autoload_register(function ($cls) {
            if (strpos($cls, "SavSpec\Plugin") !== false) {
                require_once(__DIR__ . "/Plugin.php");
            }
        });
        $sav = new Sav();
        $sav->use("\SavSpec\Plugin::install", "arg");
        $ctx = $sav->prepare("/", "GET", array());
        expect($ctx->db)->toEqual("db");
        expect($ctx->test)->toEqual("test");
    });

    it("Sav.setErrorHandler", function () {
        $sav = new Sav();
        $sav->setErrorHandler(function ($err) {
            expect($err)->toBeA("object");
            expect($err->status)->toEqual(404);
        });
        expect(function () use ($sav) {
            $sav->execute("/", "GET", array(), true);
        })->toThrow();
    });

    it("Sav.setAuthHandler", function () {
        $sav = new Sav();
        $sav->load(array(
            "modals" => array(
                array("name" => "Account")
                ),
            "actions" => array(
                array("name" => "login", "modal" => "Account", "auth" => true)
            ),
        ));
        $sav->setErrorHandler(function ($err) {
            expect($err)->toBeA("object");
            expect($err->status)->toEqual(403);
        });
        $sav->setAuthHandler(function ($route, $ctx) {
            expect($route)->toBeA('array');
            throw new \Exception("禁止访问", 403);
        });
        expect(function () use ($sav) {
            $sav->execute("/account/login", "POST", array(), true);
        })->toThrow();
    });

    it("Sav.remote", function () {
        $sav = new Sav(array(
            'remotes' => array(
                "example" => array(
                    'baseUrl' => 'http://example.com'
                )
            )
        ));
        $sav->remote('example')->load(array(
            "modals" => array(
                array("name" => "Account")
            ),
            "actions" => array(
                array("name" => "login", "modal" => "Account")
            ),
        ));
        $res = $sav->remote('example')->fetch('AccountLogin');
        expect($res)->toBeA('object');
    });

});
