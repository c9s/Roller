<?php

class RouteSetTest extends PHPUnit_Framework_TestCase
{
	function test()
	{
		$routes = new Roller\RouteSet;
		ok( $routes );

		$routes->add( '/blog/:year/:month' , function() {
			return 'Yes';
		},array( 'year' => '\d' ));

		$routes->compile();
		foreach( $routes as $r ) {
			ok( $r );
			ok( $r['compiled'] );
		}
	}

	function testMount()
	{
		$routes1 = new Roller\RouteSet;
		$routes2 = new Roller\RouteSet;

		$routes1->add( '/item' , function() { return 'item'; } );
		$routes2->add( '/subitem' , function() { return 'subitem'; });

		$routes1->mount('/item', $routes2);

		$router = new Roller\Router( $routes1 );
		$r = $router->dispatch( '/item' );
		is( 'item' , $r() );

		$r = $router->dispatch( '/item/subitem' );
		is( 'subitem' , $r() );
	}
}

