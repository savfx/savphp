<?php

use SavUtil\Request;

describe("Request", function () {
    it('Request.test', function () {
        expect(Request::test())->toEqual(true);
        expect(Request::test(true))->toEqual(true);
    });
    it('Request.fetch', function () {
        $ret = Request::fetch('http://example.com');
        expect($ret)->toBeA('object');
        expect($ret->headers)->toBeA('array');
        expect($ret->status)->toBeA('integer');
        expect($ret->response)->toBeA('string');
    });
    it('Request.fetchAll', function () {
        $ret = Request::fetchAll(array(
          "a" => array(
            "url" => 'http://example.com',
            "data" => array("a" => "b")
          ),
          "b" => array(
            "url" => 'http://example.com?m=n',
            "data" => array("a" => "b")
          ),
          "POST" => array(
            "url" => 'http://example.com?m=n',
            "type" => 'POST',
          ),
        ));
        expect($ret)->toBeA('array');
        expect($ret['a'])->toBeA('object');
        expect($ret['a']->response)->toBeA('string');
        expect($ret['b'])->toBeA('object');
        expect($ret['b']->response)->toBeA('string');
        expect($ret['POST'])->toBeA('object');
    });
    it('Request.json', function () {
        $ret = Request::fetch(array(
          'url' => 'https://api.github.com/',
          'headers' => array(
            'X-Something' => 'test'
          ),
          'options' => array(
            'dataType' => 'json'
          )
        ));
        expect($ret)->toBeA('object');
        expect($ret->headers)->toBeA('array');
        expect($ret->status)->toBeA('integer');
        expect($ret->response)->toBeA('array');
    });
});
