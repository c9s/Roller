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

    function testBuild()
    {
        $route = roller_build_route('/path/to/:blog', function($blog) {  }, array( 
            ':requirement' => array( 
                'blog' => '\w+'
            ),
        ));

        ok( is_array( $route ) , 'is an array' );

        var_dump( $route ); 
        
    }
}

