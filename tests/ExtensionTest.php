<?php

class ExtensionTest extends PHPUnit_Framework_TestCase
{


    function setup()
    {
        if( ! extension_loaded('roller') ) {
            dl('roller');
            if( ! extension_loaded('roller') )
                skip("Require roller extension to test");
        }
    }

    function testBuildRequirement()
    {
        $route = roller_build_route('/path/to/:blog', function($blog) {  }, array( 
            'blog' => '\w+',
            ':default' => array( 
                'blog' => '123'
            ),
        ));
        ok( $route );
        is( '\w+' , $route['requirement']['blog'] , 'blog requirement' );
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

