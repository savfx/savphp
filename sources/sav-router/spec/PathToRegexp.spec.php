<?php

use SavRouter\PathToRegexp;

describe("PathToRegexp", function () {

    function genericTest($tests, $name = "")
    {
      // error_log("----- " . $name . " -----");
        foreach ($tests as $id => $test) { // $path, $params, $testPath, $matchs
          // error_log("    ----- " . $id . " -----");
            $params = array();
            if (count($test) > 4) {
                $options = $test[4];
            } else {
                $options = array();
            }
            $regexp = PathToRegexp::convert($test[0], $params, $options);
          // Check the params are as expected.
            expect($test[1])->toEqual($params);
          // Run the regexp and check the result is expected.
            $matches = PathToRegexp::match($regexp, $test[2]);
            expect($test[3])->toEqual($matches);
        }
    }

  //
  // Simple paths.
  //
    it("testSimplePaths", function () {
        $tests = array(
        array('/', array(), '/', array('/')),
        array('/test', array(), '/test', array('/test')),
        array('/test', array(), '/route', null),
        array('/test', array(), '/test/route', null),
        array('/test', array(), '/test/', array('/test/')),
        array('/test/', array(), '/test', array('/test')),
        array('/test/', array(), '/test/', array('/test/')),
        array('/test/', array(), '/test//', null)
        );
        genericTest($tests, "testSimplePaths");
    });
  //
  // Case-sensitive paths.
  //
    it("testCaseSensitivePaths", function () {
        $tests = array(
        array('/test', array(), '/test', array('/test'), array("sensitive" => true )),
        array('/test', array(), '/TEST', null, array("sensitive" => true )),
        array('/TEST', array(), '/test', null, array("sensitive" => true ))
        );
        genericTest($tests, "testCaseSensitivePaths");
    });
  //
  // Strict mode.
  //
    it("testStrictMode", function () {
        $tests = array(
        array('/test', array(), '/test', array('/test'), array("strict" => true )),
        array('/test', array(), '/test/', null, array("strict" => true )),
        array('/test/', array(), '/test', null, array("strict" => true )),
        array('/test/', array(), '/test/', array('/test/'), array("strict" => true )),
        array('/test/', array(), '/test//', null, array("strict" => true ))
        );
        genericTest($tests, "testStrictMode");
    });
  //
  // Non-ending mode.
  //
    it("testNonEndingMode", function () {
        $tests = array(
        array('/test', array(), '/test', array('/test'), array("end" => false )),
        array('/test', array(), '/test/', array('/test/'), array("end" => false )),
        array('/test', array(), '/test/route', array('/test'), array("end" => false )),
        array('/test/', array(), '/test/route', array('/test'), array("end" => false )),
        array('/test/', array(), '/test//', array('/test'), array("end" => false )),
        array('/test/', array(), '/test//route', array('/test'), array("end" => false )),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("end" => false )),
        array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("end" => false ))
        );
        genericTest($tests, "testNonEndingMode");
    });
  //
  // Combine modes.
  //
    it("testCombineModes", function () {
        $tests = array(
        array('/test', array(), '/test', array('/test'), array("end" => false, "strict" => true )),
        array('/test', array(), '/test/', array('/test'), array("end" => false, "strict" => true )),
        array('/test', array(), '/test/route', array('/test'), array("end" => false, "strict" => true )),
        array('/test/', array(), '/test', null, array("end" => false, "strict" => true )),
        array('/test/', array(), '/test/', array('/test/'), array("end" => false, "strict" => true )),
        array('/test/', array(), '/test//', array('/test/'), array("end" => false, "strict" => true )),
        array('/test/', array(), '/test/route', array('/test/'), array("end" => false, "strict" => true )),
        array('/test.json', array(), '/test.json', array('/test.json'), array("end" => false, "strict" => true )),
        array('/test.json', array(), '/test.json.hbs', null, array("end" => false, "strict" => true )),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("end" => false, "strict" => true )),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', array('/route', 'route'), array("end" => false, "strict" => true )),
        array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', array('/route/', 'route'), array("end" => false, "strict" => true )),
        array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', null, array("end" => false, "strict" => true ))
        );
        genericTest($tests, "testCombineModes");
    });
  //
  // Arrays of simple paths.
  //
    it("testArraysOfSimplePaths", function () {
        $tests = array(
        array(array('/one', '/two'), array(), '/one', array('/one')),
        array(array('/one', '/two'), array(), '/two', array('/two')),
        array(array('/one', '/two'), array(), '/three', null),
        array(array('/one', '/two'), array(), '/one/two', null)
        );
        genericTest($tests, "testArraysOfSimplePaths");
    });
  //
  // Non-ending simple path.
  //
    it("testNonEndingSimplePath", function () {
        $tests = array(
        array('/test', array(), '/test/route', array('/test'), array("end" => false ))
        );
        genericTest($tests, "testNonEndingSimplePath");
    });
  //
  // Single named parameter.
  //
    it("testSingleNamedParameter", function () {
        $tests = array(
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route')),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/another', array('/another', 'another')),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/something/else', null),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route.json')),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("strict" => true )),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', null, array("strict" => true )),
        array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', array('/route/', 'route'), array("strict" => true )),
        array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route//', null, array("strict" => true )),
        array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route.json'), array("end" => false ))
        );
        genericTest($tests, "testSingleNamedParameter");
    });
  //
  // Optional named parameter.
  //
    it("testOptionalNamedParameter", function () {
        $tests = array(
        array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/route', array('/route', 'route')),
        array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/route/nested', null),
        array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/', array('/', null)),
        array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/route', array('/route', 'route'), array("strict" => true )),
        array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/', null, array("strict" => true )), // Questionable behaviour.
        array('/:test?/', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/', array('/', null),array("strict" => true )),
        array('/:test?/', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '//', null),
        array('/:test?/', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '//', null,array("strict" => true ))
        );
        genericTest($tests, "testOptionalNamedParameter");
    });
  //
  // Repeated once or more times parameters.
  //
    it("testOptionalNamedParameterRepeatedOnceOrMore", function () {
        $tests = array(
        array('/:test+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/', null),
        array('/:test+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/route', array('/route', 'route')),
        array('/:test+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/some/basic/route', array('/some/basic/route', 'some/basic/route')),
        array('/:test(\\d+)+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/abc/456/789', null),
        array('/:test(\\d+)+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/123/456/789', array('/123/456/789', '123/456/789')),
        array('/route.:ext(json|xml)+', array(array("name" => 'ext', "delimiter" => '.', "optional" => false, "repeat" => true )), '/route.json', array('/route.json', 'json')),
        array('/route.:ext(json|xml)+', array(array("name" => 'ext', "delimiter" => '.', "optional" => false, "repeat" => true )), '/route.xml.json', array('/route.xml.json', 'xml.json')),
        array('/route.:ext(json|xml)+', array(array("name" => 'ext', "delimiter" => '.', "optional" => false, "repeat" => true )), '/route.html', null)
        );
        genericTest($tests, "testOptionalNamedParameterRepeatedOnceOrMore");
    });
  //
  // Repeated zero or more times parameters.
  //
    it("testRepeatedZeroOrMoreParameters", function () {
        $tests = array(
        array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '/', array('/', null)),
        array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '//', null),
        array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '/route', array('/route', 'route')),
        array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '/some/basic/route', array('/some/basic/route', 'some/basic/route')),
        array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route', array('/route', null)),
        array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route.json', array('/route.json', 'json')),
        array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route.xml.json', array('/route.xml.json', 'xml.json')),
        array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route.123', null)
        );
        genericTest($tests, "testRepeatedZeroOrMoreParameters");
    });
  //
  // Custom named parameters.
  //
    it("testCustomNamedParameters", function () {
        $tests = array(
        array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123', array('/123', '123')),
        array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/abc', null),
        array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123/abc', null),
        array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123/abc', array('/123', '123'), array("end" => false )),
        array('/:test(.*)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )),'/anything/goes/here', array('/anything/goes/here', 'anything/goes/here')),
        array('/:route([a-z]+)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/abcde', array('/abcde', 'abcde')),
        array('/:route([a-z]+)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/12345', null),
        array('/:route(this|that)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/this', array('/this', 'this')),
        array('/:route(this|that)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/that', array('/that', 'that'))
        );
        genericTest($tests, "testCustomNamedParameters");
    });
  //
  // Prefixed slashes could be omitted.
  //
    it("testPrefixedSlashed", function () {
        $tests = array(
        array('test', array(), 'test', array('test')),
        array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route', array('route', 'route')),
        array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', null),
        array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route/', array('route/', 'route')),
        array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route/', null, array("strict" => true )),
        array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route/', array('route/', 'route'), array("end" => false ))
        );
        genericTest($tests, "testPrefixedSlashed");
    });
  //
  // Formats.
  //
    it("testFormats", function () {
        $tests = array(
        array('/test.json', array(), '/test.json', array('/test.json')),
        array('/test.json', array(), '/route.json', null),
        array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route')),
        array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json.json', array('/route.json.json', 'route.json')),
        array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route'), array("end" => false )),
        array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json.json', array('/route.json.json', 'route.json'), array("end" => false ))
        );
        genericTest($tests, "testFormats");
    });
  //
  // Format params.
  //
    it("testFormatParams", function () {
        $tests = array(
        array('/test.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.html', array('/test.html', 'html')),
        array('/test.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.hbs.html', null),
        array('/test.:format.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ),   array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/test.hbs.html', array('/test.hbs.html', 'hbs', 'html')),
        array('/test.:format+', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => true ) ), '/test.hbs.html', array('/test.hbs.html', 'hbs.html')),
        array('/test.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.hbs.html', null, array("end" => false )),
        array('/test.:format.', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.hbs.html', null, array("end" => false ))
        );
        genericTest($tests, "testFormatParams");
    });
  //
  // Format and path params.
  //
    it("testFormatAndPathParams", function () {
        $tests = array(
        array( '/:test.:format', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/route.html', array('/route.html', 'route', 'html')),
        array( '/:test.:format', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/route', null),
        array( '/:test.:format', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/route', null),
        array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route', array('/route', 'route', null)),
        array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route.json', array('/route.json', 'route', 'json')),
        array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route', array('/route', 'route', null), array("end" => false )),
        array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route.json', array('/route.json', 'route', 'json'), array("end" => false )),
        array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route.json.html', array('/route.json.html', 'route.json', 'html'), array("end" => false )),
        array( '/test.:format(.*)z', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.abc', null, array("end" => false )),
        array( '/test.:format(.*)z', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.abcz', array('/test.abcz', 'abc'), array("end" => false ))
        );
        genericTest($tests, "testFormatAndPathParams");
    });

    it("testUnnamedParams", function () {
        $tests = array(
        array( '/(\\d+)',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/123',
        array('/123', '123')
        ),
        array( '/(\\d+)',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/abc',
        null
        ),
        array( '/(\\d+)',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/123/abc',
        null
        ),
        array( '/(\\d+)',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/123/abc',
        array('/123', '123'),
        array("end" => false )
        ),
        array( '/(\\d+)',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/abc',
        null,
        array("end" => false )
        ),
        array( '/(\\d+)?',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => true, "repeat" => false )
        ),
        '/',
        array('/', null)
        ),
        array( '/(\\d+)?',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => true, "repeat" => false )
        ),
        '/123',
        array('/123', '123')
        ),
        array( '/(.*)',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/route',
        array('/route', 'route')
        ),
        array( '/(.*)',
        array(
          array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/route/nested',
        array('/route/nested', 'route/nested')
        )
        );
        genericTest($tests, "testUnnamedParams");
    });

    it("testCorrectNamesAndIndexes", function () {
        $tests = array(
        array(
        array('/:test', '/route/:test'),
        array(
          array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ),
          array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/test',
        array('/test', 'test', null)
        ),
        array(
        array('/:test', '/route/:test'),
        array(
          array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ),
          array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/route/test',
        array('/route/test', null, 'test')
        )
        );
        genericTest($tests, "testCorrectNamesAndIndexes");
    });

    it("testRespectEscapedCharacters", function () {
        $tests = array(
        array('/\\(testing\\)', array(), '/testing', null),
        array('/\\(testing\\)', array(), '/(testing)', array('/(testing)')),
        array('/.+*?=^!:${}[]|', array(), '/.+*?=^!:${}[]|', array('/.+*?=^!:${}[]|'))
        );
        genericTest($tests, "testRespectEscapedCharacters");
    });

    it("testRegressions", function () {
        $tests = array(
        array(
        '/:remote([\\w-.]+)/:user([\\w-]+)',
        array(
          array("name" => 'remote', "delimiter" => '/', "optional" => false, "repeat" => false ),
          array("name" => 'user', "delimiter" => '/', "optional" => false, "repeat" => false )
        ),
        '/endpoint/user',
        array('/endpoint/user', 'endpoint', 'user')
        )
        );
        genericTest($tests, "testRegressions");
    });
});
