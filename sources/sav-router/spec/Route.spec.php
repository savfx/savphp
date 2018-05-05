<?php

use SavRouter\Route;

describe("Route", function () {
    it('Route.parse', function () {
        $trustOpts = array(
            'sensitive' => true,
            'end' => true,
            'strict' => true
        );
        $route = Route::parse('a', $trustOpts);
        expect(Route::match($route, 'a'))->toBeA('array');
        expect(Route::match($route, '/a'))->toEqual(null);
        expect(Route::match($route, 'a/'))->toEqual(null);
        $route = Route::parse('/a', $trustOpts);
        expect(Route::match($route, '/a'))->toBeA('array');
        expect(Route::match($route, 'a'))->toEqual(null);
        expect(Route::match($route, 'a/'))->toEqual(null);
        $route = Route::parse('a/', $trustOpts);
        expect(Route::match($route, 'a/'))->toBeA('array');
        expect(Route::match($route, 'a'))->toEqual(null);
        expect(Route::match($route, '/a'))->toEqual(null);
        $route = Route::parse(':a', $trustOpts);
        expect(Route::match($route, 'a'))->toBeA('array');
        expect(Route::match($route, '/a'))->toEqual(null);
        expect(Route::match($route, 'a/'))->toEqual(null);
        $route = Route::parse('/:a', $trustOpts);
        expect(Route::match($route, '/a'))->toBeA('array');
        expect(Route::match($route, 'a'))->toEqual(null);
        expect(Route::match($route, 'a/'))->toEqual(null);
        $route = Route::parse(':a?', $trustOpts);
        expect(Route::match($route, 'a'))->toBeA('array');
        expect(Route::match($route, ''))->toBeA('array');
        expect(Route::match($route, '/a'))->toEqual(null);
        expect(Route::match($route, 'a/'))->toEqual(null);
        $route = Route::parse(':a/:b?', $trustOpts);
        expect(Route::match($route, 'a'))->toBeA('array');
        expect(Route::match($route, 'a/b'))->toBeA('array');
        expect(Route::match($route, 'a/b/'))->toEqual(null);
        expect(Route::match($route, '/a/b/'))->toEqual(null);
        $route = Route::parse(':a/:b', $trustOpts);
        expect(Route::match($route, 'a/b'))->toBeA('array');
        expect(Route::match($route, 'a/b/'))->toEqual(null);
        expect(Route::match($route, '/a/b/'))->toEqual(null);
        $route = Route::parse(':a-:b', $trustOpts);
        expect(Route::match($route, 'a-b'))->toBeA('array');
        expect(Route::match($route, 'a-b/'))->toEqual(null);
        expect(Route::match($route, '/a-b'))->toEqual(null);
        expect(Route::match($route, '/a-b/'))->toEqual(null);
        $route = Route::parse(':a-:b?', $trustOpts);
        expect(Route::match($route, 'a-b'))->toBeA('array');
        expect(Route::match($route, 'a-'))->toBeA('array');
        expect(Route::match($route, 'a'))->toEqual(null);
        expect(Route::match($route, 'a-b/'))->toEqual(null);
        expect(Route::match($route, '/a-'))->toEqual(null);
        expect(Route::match($route, '/a-b'))->toEqual(null);
        expect(Route::match($route, '/a-b/'))->toEqual(null);
        $route = Route::parse(':a', array("end" => false));
        expect(Route::match($route, 'a'))->toBeA('array');
        expect(Route::match($route, 'a/'))->toBeA('array');
        expect(Route::match($route, 'a/b'))->toBeA('array');
        expect(Route::match($route, '/a'))->toEqual(null);
        $route = Route::parse('/home/:path?', array("sensitive" => true));
        expect(Route::match($route, '/home'))->toBeA('array');
        expect(Route::match($route, '/home/a'))->toBeA('array');
        expect(Route::match($route, '/HOME'))->toEqual(null);
        expect(Route::match($route, '/HOME/a'))->toEqual(null);
        $route = Route::parse('/home/:path?', array("sensitive" => false));
        expect(Route::match($route, '/home'))->toBeA('array');
        expect(Route::match($route, '/home/a'))->toBeA('array');
        expect(Route::match($route, '/HOME'))->toBeA('array');
        expect(Route::match($route, '/HOME/a'))->toBeA('array');
    });

    it('Route.complie', function () {
        $make = Route::complie('test');
        expect($make())->toEqual('test');
        $make = Route::complie('/test');
        expect($make())->toEqual('/test');
        $make = Route::complie(':a');
        expect($make(array("a" => 1)))->toEqual('1');
        expect($make(array("a" => 's')))->toEqual('s');
        expect($make(array("a" => 's b')))->toEqual('s%20b');
        expect($make(array("a" => 's:b')))->toEqual('s%3Ab');
        expect($make(array("a" => 's/b')))->toEqual('s%2Fb');
        expect($make(array("a" => 's;b')))->toEqual('s%3Bb');
        expect($make(array("a" => 's?b')))->toEqual('s%3Fb');
        expect($make(array("a" => true)))->toEqual('true');
        $make = Route::complie('/:a');
        expect($make(array("a" => 1)))->toEqual('/1');
        expect($make(array("a" => 's')))->toEqual('/s');
        expect($make(array("a" => 's b')))->toEqual('/s%20b');
        expect($make(array("a" => true)))->toEqual('/true');
        $make = Route::complie('/:a/:b');
        expect($make(array("a" => 1, "b" => 2)))->toEqual('/1/2');
        expect($make(array("a" => 's', "b" => 'b')))->toEqual('/s/b');
        expect($make(array("a" => 's b', "b" => 'c')))->toEqual('/s%20b/c');
        expect($make(array("a" => true, "b" => false)))->toEqual('/true/false');
        $make = Route::complie('/:a?');
        expect($make())->toEqual('');
        expect($make(array("a" => 1)))->toEqual('/1');
        expect($make(array("a" => 's')))->toEqual('/s');
        expect($make(array("a" => 's b')))->toEqual('/s%20b');
        expect($make(array("a" => true)))->toEqual('/true');
        $make = Route::complie('/:a/:b?');
        expect($make(array("a" => 1)))->toEqual('/1');
        expect($make(array("a" => 1, "b" => 2)))->toEqual('/1/2');
        expect($make(array("a" => 's', "b" => 'b')))->toEqual('/s/b');
        expect($make(array("a" => 's b', "b" => 'c')))->toEqual('/s%20b/c');
        expect($make(array("a" => true, "b" => false)))->toEqual('/true/false');

        expect(function () use ($make) {
            $make();
        })->toThrow();

        $route = Route::parse('/home/:path?');
        $make = Route::complie($route['tokens']);
        expect($make())->toEqual('/home');
        expect($make(array("path" => 1)))->toEqual('/home/1');
    });
});
