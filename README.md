Roller
======

PHP Roller is a simple router tool for routing paths for PHP5.3+.

Roller API is really simple, eazy to use.

SYNOPSIS
--------

Initialize a router:

    $router = new Roller\Router;

Add new route with a simple callback

    $router->add( '/path/to/blog'  , function() { 
        return 'Blog';
    });

Add new route with a class callback

    $router->add( '/path/to/blog'  , array('Controller','method') );

Add new route with a class/action callback string:

    $router->add( '/path/to/blog'  , 'Controller:methodName' );

Add new route with requirement:

    $router->add( '/path/to/:year' , array('Callback','method') , array( 
        'year' => '\d+',
        ':default' => array(
            'year' => 2000,
        ),
    ));

Dispatch
--------
To dispatch path:

    $r = $router->dispatch( $_SERVER['PATH_INFO'] );

To evalulate response content:

    if( $r !== false )
        echo $r();
    else
        die('page not found.');


Mount paths
-----------

To mount route set:

    $routes = new Roller\RouteSet;
    $routes->add( '/path/to/:year' , array( 'Callback', 'method' ) );

    $routes = new Roller\RouteSet;
    $routes->mount( '/root' , $routes );

    $router = new Roller\Router( $routes );

Cache
-----

To enable apc cache:

    $router = new Roller\Router( null , array( 
        'cache_id' => '_router_testing_'
    ));
    
To enable file cache:

    $router = new Roller\Router( null , array( 
        'cache_id' => '_router_testing_',
        'cache_dir' => 'tests/cache',
    ));

