<?php

class MatchedRouteTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $router = new Roller\Router;
        $router->any('/hello', function() { return 'Hello'; } );
        $matched = $router->dispatch( '/hello' );
        ok($matched);
        ok(is_callable($matched));

        ok( $matched['callback'] );

        is( 'Hello', $matched() );
    }
}

