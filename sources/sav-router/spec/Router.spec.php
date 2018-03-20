<?php

use SavRouter\Router;

describe("Router", function() {
  
  it("Router.declare", function() {
    $router = new Router();
    $m = $router->declare(array("name" => "Account"));
    $a = $router->declare(array("name" => "login", "modal" => "Account"));
    expect($m)->toBeA('array');
    expect($a)->toBeA('array');
  });

  it('stripPrefix', function () {
    expect(SavRouter\stripPrefix('', ''))->toEqual('');
    expect(SavRouter\stripPrefix('/', ''))->toEqual('/');
    expect(SavRouter\stripPrefix('/a', '/a'))->toEqual('/');
    expect(SavRouter\stripPrefix('/a/', '/a'))->toEqual('/');
    expect(SavRouter\stripPrefix('/a/b', '/a'))->toEqual('/b');

    expect(SavRouter\stripPrefix('/a/', '/a/'))->toEqual('/');
    expect(SavRouter\stripPrefix('/a/b', '/a/'))->toEqual('/b');
  });

  it("Router.basic", function() {
    $router = new Router(array(
      'prefix' => '',
      'caseType' => 'camel',
      'method' => 'GET',
      'sensitive' => true,
    ));
    $router->load(array("modals" => array(
      "Home" => 
        array("routes" => array(
            "default" => array(), 
            "relative" => array("path" => 'relativeRoute'), 
            "absolute" => array("path" => '/absoluteRoute'), 
            "user" => array("path" => 'user/:id')
          )
        ),
      "Article" => 
        array("path" => 'art', 
          "routes" => array(
            "list" => array(), 
            "cat" => array("path" => '/article/cat/:id'), 
            "item" => array("path" => 'item/:id')
          )
        )
      )
    ));
    $pathEqual = function ($path, $end = false) use ($router){
      $ret = $router->matchRoute($path, 'GET');
      expect($ret)->toBeA('array');
      expect($ret['route'])->toBeA('array');
      if (is_string($end)) {
        expect($ret['route']['path'])->toEqual($end);
      } else {
        expect($end ? ($ret['route']['path'] . '/') : $ret['route']['path'])->toEqual($path);
      }
    };

    $pathEqual('/home/default');
    $pathEqual('/home/default/', true);
    $pathEqual('/home/relativeRoute');
    $pathEqual('/home/relativeRoute/', true);
    $pathEqual('/absoluteRoute');
    $pathEqual('/absoluteRoute/', true);
    $pathEqual('/home/user/1', '/home/user/:id');
    $pathEqual('/home/user/1/', '/home/user/:id');

  });

  it("Router.sensitive", function() {
    $router = new Router(array(
      'caseType' => 'hyphen',
      'method' => 'GET',
      'sensitive' => false,
    ));
    $router->load(array("modals" => array(
      "UserProfile" => 
        array("routes" => array(
            "HomeInfo" => array(), 
            "UserAddress" => array("path" => 'UserAddress'), 
          )
        ),
      )
    ));
    expect($router->matchRoute('/user-profile/home-info', 'GET'))->toBeA('array');
    expect($router->matchRoute('/user-PROFILE/HOME-info/', 'GET'))->toBeA('array');
    expect($router->matchRoute('/user-profile/HomeInfo', 'GET'))->toBe(null);
    expect($router->matchRoute('/UserProfile/home-info', 'GET'))->toBe(null);
    expect($router->matchRoute('/UserProfile/HomeInfo', 'GET'))->toBe(null);
    expect($router->matchRoute('/user-profile/UserAddress', 'GET'))->toBeA('array');
  });

  it("Router.load", function() {
    $router = new Router(array(
      'caseType' => 'hyphen',
      'method' => 'GET',
      'sensitive' => false,
    ));
    $router->load(array(
      "modals" => array(
        array("id" => 1, "name" => "UserProfile", "routes" => array(
          array("name" => "UserAddress")
        ))
      ),
      "actions" => array(
        array("name" => "HomeInfo", "modal" => 1),
      ),
    ));
    expect($router->matchRoute('/user-profile/home-info', 'GET'))->toBeA('array');
    expect($router->matchRoute('/user-PROFILE/HOME-info/', 'GET'))->toBeA('array');
    expect($router->matchRoute('/user-profile/HomeInfo', 'GET'))->toBe(null);
    expect($router->matchRoute('/UserProfile/home-info', 'GET'))->toBe(null);
    expect($router->matchRoute('/UserProfile/HomeInfo', 'GET'))->toBe(null);
    expect($router->matchRoute('/user-profile/user-address', 'NONE'))->toBe(null);
    expect($router->matchRoute('/user-profile/user-address', 'OPTIONS'))->toBeA('array');
    expect($router->matchRoute('/user-profile/user-address', 'GET'))->toBeA('array');
  });
});
