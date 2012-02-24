<?php

class RouteSetTest extends PHPUnit_Framework_TestCase
{
	function test()
	{
		$routes = new Roller\RouteSet;
		ok( $routes );

		$routes->add( '/blog/{year}/{month}' , function() {
			return 'Yes';
		},array( 
			'year' => '\d'
	   	));

		foreach( $routes as $r ) {
			ok( $r );
			ok( $r['compiled'] );
		}
	}
}

