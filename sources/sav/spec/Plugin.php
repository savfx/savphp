<?php
namespace SavSpec;

class Plugin
{
    static function install($sav, $ctx, $arg)
    {
        expect($arg)->toEqual("arg");
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
    }
}
