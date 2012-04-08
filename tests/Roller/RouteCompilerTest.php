<?php

class RouteCompilerTest extends PHPUnit_Framework_TestCase
{

    function testOptional()
    {
        $route = Roller\RouteCompiler::compile(array( 
            'path' => '/blog/:id(.:format)',
            'default' => array(
                'format' => 'json'
            )
        ));

        ok( preg_match( $route['compiled'] , '/blog/23.json', $matched ) );
        is( '23'   , $matched['id'] );
        is( 'json' , $matched['format'] );

        ok( preg_match( $route['compiled'] , '/blog/31.xml', $matched ) );
        is( '31'  , $matched['id'] );
        is( 'xml' , $matched['format'] );

        ok( preg_match( $route['compiled'] , '/blog/foo.yaml', $matched ) );
        is( 'foo'  , $matched['id'] );
        is( 'yaml' , $matched['format'] );


        ok( preg_match( $route['compiled'] , 
            '/blog/24.json', $matched ) );
        is( 'json', $matched['format'] );

        ok( ! preg_match( $route['compiled'] , 
            '/blog/23/json' ) );

        ok( ! preg_match( $route['compiled'] , 
            '/blog/23/' ) );
    }


    function testOptionalHolder()
    {
        $route = Roller\RouteCompiler::compile(array( 
            'path' => '/blog/:id.:format',
            'default' => array(
                'format' => 'json'
            )
        ));
        ok( preg_match( $route['compiled'] , '/blog/23.json', $matched ) );
        is( 23, $matched['id'] );
        is( 'json' , $matched['format'] );

        ok( preg_match( $route['compiled'] , '/blog/23', $matched ) );
        is( 23, $matched['id'] );
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

