<?php

class TestController extends Roller\Controller
{
	function run($id)
	{
		return 'Test ' . $id;
	}
}

class MyController extends Roller\Controller
{
    public function before() 
    {

    }

    public function run()
    {
        return 'rocks';
    }

    public function after()
    {

    }
}

class RouterTest extends PHPUnit_Framework_TestCase
{

    function testController()
    {
        $router = new Roller\Router;
        $router->add('/test' , 'MyController:indexAction' );
        $route = $router->dispatch('/test');
        ok( $route );
    }


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
		is('Hello 2012', $content , 'default value should work' );

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

    function testRequestMethod()
    {
		$router = new Roller\Router;
        $router->add('/item/:id', 'TestController:run', array( 
            ':post' => true,
        ));
        $_SERVER['REQUEST_METHOD'] = 'POST';
		$r = $router->dispatch( '/item/9' );
		is('Test 9', $r() );

        $_SERVER['REQUEST_METHOD'] = 'GET';
		$r = $router->dispatch( '/item/9' );
        ok( $r === false );

        $_SERVER['REQUEST_METHOD'] = 'HEAD';
		$r = $router->dispatch( '/item/9' );
        ok( $r === false );
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

