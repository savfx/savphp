<?php
use Sav\Remote;

describe("Remote", function () {

    it("Remote.base", function () {
        $remote = new Remote(array(
            'baseUrl' => 'http://www.example.com'
        ));
        $remote->load(array(
            "modals" => array(
                array("name" => "Account")
            ),
            "actions" => array(
                array("name" => "login", "modal" => "Account"),
                array("name" => "register", "modal" => "Account"),
            ),
        ));
        $ret = $remote->fetch("AccountLogin", array());
        expect($ret->response)->toBeA("string");

        $ret = $remote->fetchAll(array(
            "AccountLogin" => array("a" => "b")
        ));
        expect($ret)->toBeA("array");
        expect($ret['AccountLogin']->response)->toBeA("string");
        
        $ret = $remote->action("AccountLogin")->queue()
            ->action("AccountLogin")->queue()
            ->queue("AccountLogin")
            ->fetchAll(array("AccountLogin" => array()));
        expect($ret)->toBeA("array");
        expect(count($ret))->toEqual(4);
    });
});
