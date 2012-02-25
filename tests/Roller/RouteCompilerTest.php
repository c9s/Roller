<?php

class RouteCompilerTest extends PHPUnit_Framework_TestCase
{
    function testOptionalHolder()
    {
        $route = Roller\RouteCompiler::compile(array( 
            'path' => '/blog/:id.:format'
        ));
        ok( preg_match( $route['compiled'] , '/blog/23.json', $matched ) );
        is( 23, $matched['id'] );
        is( 'json' , $matched['format'] );
    }

    function testPlaceHolder()
    {
        $route = Roller\RouteCompiler::compile(array( 
            'path' => '/blog/:year/:month'
        ));
        $pattern = '#^
    /blog
    /(?P<year>[^/]+?)
    /(?P<month>[^/]+?)
$#xs';
        is($pattern,$route['compiled']);
    }
}

