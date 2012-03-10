<?php

class ExtensionTest extends PHPUnit_Framework_TestCase
{


    function setup()
    {
        if( ! extension_loaded('roller') ) {
            skip("Require roller extension to test");
        }
    }

    function testBuildName()
    {
        $route = roller_build_route('/path/to/:blog', function($blog) {  }, array( 
            'blog' => '\w+',
            ':secure' => true,
            ':post' => true,
            ':default' => array( 
                'blog' => '123'
            ),
        ));
        ok( $route );
        is( 'path_to_blog', $route['name'] );
        ok( $route['callback'] );
        ok( $route['secure'] );
    }



    function testBuildRequirement()
    {
        $route = roller_build_route('/path/to/:blog', function($blog) {  }, array( 
            'blog' => '\w+',
            ':secure' => true,
            ':post' => true,
            ':name' => 'blog_route',
            ':default' => array( 
                'blog' => '123'
            ),
        ));
        ok( $route );
        is( '\w+' , $route['requirement']['blog'] , 'blog requirement' );
        ok( $route['secure'] );
        ok( $route['default'] );
        is( 123, $route['default']['blog'] );
        is( 'post' , $route['method'] );
        is( 'blog_route' , $route['name'] );
    }


        

    function testBuildRoute()
    {
        $route = roller_build_route('/path/to/:blog', function($blog) {  }, array( 
            ':requirement' => array( 
                'blog' => '\w+'
            ),
        ));

        ok( is_array( $route ) , 'is an array' );
        ok( isset($route['requirement'] ) );
        ok( isset($route['requirement']['blog'] ) );
        is( '\w+', $route['requirement']['blog'] );
    }
}

