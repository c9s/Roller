<?php
/**
 * @requires extension apc
 */
class RouterApcTest extends PHPUnit_Framework_TestCase
{
    function setup()
    {
        if( ! extension_loaded('apc') )
            skip('apc required.');
    }

	function testApcCache()
	{
		ini_set('apc.enable_cli',1);

		apc_store( 'test', 3 );
		is( 3, apc_fetch( 'test' ) );

		$router = new Roller\Router( null , array( 
			'cache_id' => '_router_testing_'
	   	));
		is( Roller\Router::cache_type_apc , $router->cache );

		$router->add('/item/:id', function($id) { return $id; });
		$r = $router->dispatch( '/item/12' );

		$code = apc_fetch( '_router_testing_' );
		ok( $code );
		$routes = eval($code);
		ok( $routes );
		ok( $routes->routes );



		// make sure cache reload works
		$router = new Roller\Router( null , array( 
			'cache_id' => '_router_testing_'
	   	));
		$r = $router->dispatch( '/item/12' );
		is( '12' , $r() );
		apc_delete( '_router_testing_' );
	}
}
