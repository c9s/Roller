Roller
======

PHP Roller is a simple/fast router for PHP5.3.

Roller API is really simple, eazy to use.

<img src="https://github.com/c9s/Roller/raw/master/misc/img1.png" width="500">

INSTALL
-------

Clone this repository, pick up a PSR-0 classloader, and add `src/` to
class path.

To use console dumper, you will need ezc/ConsoleTools, please use PEAR
installer to install:

    sudo pear channel-discover components.ez.no
    sudo pear install -a ezc/ConsoleTools

SYNOPSIS
--------

Initialize a router:

    $router = new Roller\Router;

Add a new route with simple callback

    $router->add( '/path/to/blog'  , function() { 
        return 'Blog';
    });

Add a new route with class callback

    $router->add( '/path/to/blog'  , array('Controller','method') );

Add a new route with class/action callback string:

    $router->add( '/path/to/blog'  , 'Controller:methodName' );

To add a new route with requirement:

    $router->add( '/path/to/:year' , array('Callback','method') , array( 
        'year' => '\d+',
    ));

META ATTRIBUTE
--------------
Meta attributes are started without ':' prefix. currently, Roller supports: 
`method`, `default`, `requirement`, `post`, `get` attributes.

To add a new route with requirement and default value:

    $router->add( '/path/to/:year' , array('Callback','method') , array( 
        'year' => '\d+',
        ':default' => array(
            'year' => 2000,
        ),
    ));

To add a new route with request method (like POST method):

    $router->add( '/path/to/:year' , array('Callback','method') , array( 
        'year' => '\d+',

        ':method' => 'post,
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



Hacking
-------
Get Onion and install it:

    $ curl -s http://install.onionphp.org/ | bash

Run onion to install depedencies:

    $ onion bundle

Now you should be able to run phpunit =)

    $ phpunit tests

