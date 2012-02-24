<?php

class TestController extends Roller\Controller
{
	function run($id)
	{
		return 'Test ' . $id;
	}
}

class RouterTest extends PHPUnit_Framework_TestCase
{
	function test()
	{
		$router = new Roller\Router;
		ok( $router );

		$router->add('/blog/:year', function($year) { 
			return 'Hello ' . $year;
		}, array(
			'year' => '\d+',
			':default' => array( 
				'year' => 2012
		   	)
	   	));

		$r = $router->dispatch('/blog/2011');
		ok( $r !== false );
		ok( $r );
		$content = $r();
		is('Hello 2011', $content );

		$r = $router->dispatch('/blog');
		ok( $r !== false );
		ok( $r );
		$content = $r();
		is('Hello 2012', $content , 'default value' );

		$r = $router->dispatch('/path/to/another');
		ok( $r === false , 'route not found.' );
	}

	function testController()
	{
		$router = new Roller\Router;
		$router->add('/item/:id', array('TestController','run'));
		$r = $router->dispatch( '/item/3' );
		is('Test 3', $r() );
	}

	function testControllerString()
	{
		$router = new Roller\Router;
		$router->add('/item/:id', 'TestController:run');
		$r = $router->dispatch( '/item/4' );
		is('Test 4', $r() );
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


	function testFileCache()
	{
		if( ! file_exists('tests/cache') )
			mkdir( 'tests/cache', 0755, true );

		$router = new Roller\Router( null , array( 
			'cache_id' => '_router_testing_',
			'cache_dir' => 'tests/cache',
	   	));
		is( Roller\Router::cache_type_file , $router->cache );
		$router->add('/item/:id', function($id) { return $id; });
		$r = $router->dispatch( '/item/12' );
		ok( $r );
		is( '12', $r() );

		ok(file_exists( 'tests/cache/_router_testing_' ));


		// the cache should be reloaded.
		$router = new Roller\Router( null , array( 
			'cache_id' => '_router_testing_',
			'cache_dir' => 'tests/cache',
	   	));
		$r = $router->dispatch( '/item/12' );
		ok( $r );
		is( '12', $r() );

		unlink( 'tests/cache/_router_testing_' );
	}

}

