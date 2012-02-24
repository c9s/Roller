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
		ini_set('apc.enable_cli',true);

		$router = new Roller\Router( null , array( 
			'cache_id' => '_router_testing_'
	   	));
		is( Roller\Router::cache_type_apc , $router->cache );

		$router->add('/item/:id', function($id) { return $id; });
		$router->dispatch( '/item/12' );

		$code = apc_fetch( '_router_testing_' );
		ok( $code );
	}

}

