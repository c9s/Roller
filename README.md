Roller
======

PHP Roller is a simple/fast router for PHP5.3.

Roller uses simple array to store routes, so it's fast.

[![Build Status](https://secure.travis-ci.org/c9s/Roller.png)](http://travis-ci.org/c9s/Roller)

FEATURES
--------

- Highly customizable
- Flexible
- APC cache support.
- File cache support.
- Built-in **RESTful** route generator, resource handler.
- Customizable RESTful route generator, resource handler.
- Simple, Useful route path syntax. (rails-style)
- High performance (through PHP extension, can dispatch **1607%** faster than pure php version)
- High unit test coverage, coverage > **88%**.
- Ready for Frameworks.



ROUTE CONSOLE DUMPER
--------------------

<img src="https://github.com/c9s/Roller/raw/master/misc/img1.png" width="500">

INSTALL
-------

Install through PEAR:

    $ sudo pear channel-discover pear.corneltek.com
    $ sudo pear install corneltek/Roller

Or clone this repository:

    $ sudo pear install -f package.xml

Use thie library as a git submodule: clone this repository, pick up a PSR-0 classloader, and add `src/` to class
path.

To use console dumper, you need ezc/ConsoleTools, please use PEAR
installer to install:

    $ sudo pear channel-discover components.ez.no
    $ sudo pear install -a ezc/ConsoleTools

SYNOPSIS
--------

Initialize a router:

```php
<?php
    $router = new Roller\Router;
```

Add a new route with simple callback

```php
<?php
    $router->add( '/blog/:id/:title'  , function($id,$title) { 
        return 'Blog';
    });
```

Add a new route with class callback

```php
<?php
    $router->add( '/blog/:year/:month/:id/:title'  , array('Controller','method') );
```

Add a new route with class/action callback string:

```php
<?
    $router->add( '/path/to/blog'  , 'Controller:methodName' );
```

To add a new route with requirement:

```php
<?
    $router->add( '/path/to/:year' , array('Callback','method') , array( 
        'year' => '\d+',
    ));
```

To add a new route with requirement and closure:

```php
<?
    $router->add( '/path/to/:year' , function($year) { 
        return $year;
    },array( 
        'year' => '\d+',
    ));
```

Or by `any`:

```php
<?
    $router->any( '/path/to/:year' , function($year) { 
        return $year;
    }, array( 
        'year' => '\d+',
    ));
```

An alias for GET method:

```php
<?
    $router->get( '/path/to/:year' , function($year) { ... } );
```

An alias for POST method:

```php
<?
    $router->post( '/path/to/:year' , function($year) { ... } );
```


META ATTRIBUTE
--------------
Meta attributes are started with ':' prefix. currently, Roller currently
supports: `:method`, `:default`, `:requirement`, `:post`, `:get` attributes.

To add a new route with requirement and default value:

```php
<?
    $router->add( '/path/to/:year' , array('Callback','method') , array( 
        'year' => '\d+',
        ':default' => array(
            'year' => 2000,
        ),
    ));
```

To add a new route with request method (like POST method):

```php
<?
    $router->add( '/path/to/:year' , array('Callback','method') , array( 
        'year' => '\d+',

        ':method' => 'post,
        ':default' => array(
            'year' => 2000,
        ),
    ));
```

RouteSet
--------

RouteSet is a route collection class, you can mount a route set to another route set.

To use RouteSet is very easy:

```php
<?php
$subroutes = new Roller\RouteSet;
$subroutes->add( '/subitem' , $cb );

$routes = new Roller\RouteSet;
$routes->mount( '/item' , $subroutes );
```

Mount paths
-----------

To mount route set:

```php
<?
    $routes = new Roller\RouteSet;
    $routes->add( '/path/to/:year' , array( 'Callback', 'method' ) );

    $routes = new Roller\RouteSet;
    $routes->mount( '/root' , $routes );

    $router = new Roller\Router( $routes );
```

Dispatch
--------

To dispatch path:

```php
<?
    $r = $router->dispatch( $_SERVER['PATH_INFO'] );
```

To evalulate response content:

```php
<?
    if( $r !== false )
        echo $r();
    else
        die('page not found.');
```

Customize Route Class
---------------------

```php
<?
class YourRoute extends Roller\MatchedRoute
{
    // customze here.
}

$r = new Roller\Router(array( 
    'route_class' => 'YourRoute'
));
$route = $r->dispatch( '/path/to/...' );    // get YourRoute object.
```


Cache
-----

To enable apc cache:

```php
<?
    $router = new Roller\Router( null , array( 
        'cache_id' => '_router_testing_'
    ));
```
    
To enable file cache:

```php
<?
    $router = new Roller\Router( null , array( 
        'cache_id' => '_router_testing_',
        'cache_dir' => 'tests/cache',
    ));
```

RESTful
-------

Roller Router has a built-in RESTful route generator, it's pretty easy to
define a bunch of RESTful routes, Roller Router also provides a simple RESTful
route generator, which is pretty easy to customize your own RESTful routes.

First, initalize a RESTful plugin object:

```php
<?php
        $router = new Roller\Router;
        $restful = new Roller\Plugin\RESTful(array( 
				'prefix' => '/restful' 
		));
```

Add RESTful plugin to your router manager:

```php
<?php
        $router->addPlugin($restful);
```

To support RESTful, you have two solutions:

- `ResourceHandler`: If you need to define differnt logic for each resource, you can use
  `ResourceHandler`, you can separate different resource logic into different
  handler class.
- `GenericHandler`: If your resources use the same logic and the same
  permission controll, you can use `GenericHandler` for every resources.


To use ResourceHandler, register your resource id to router with your resource
handler class name, each resource id is mapping to an resource handler:

```php
<?php
        $restful->registerResource( 'blog' , 'BlogResourceHandler' );
```

Define your resource handler, here is a simple blog example that defines how
RESTful CRUD works:

```php
	<?php
	use Roller\Plugin\RESTful\ResourceHandler;

	class BlogResourceHandler extends ResourceHandler
	{
		public function create()    { return array( 'id' => 1 ); }

		public function update($id) { return array( 'id' => 1 ); }

		// delete a record.
		public function delete($id) { return array( 'id' => 1 ); }

		// load one record
		public function load($id)   { return array( 'id' => $id , 'title' => 'title' ); }

		// find records
		public function find()      { 
			return array( 
				array( 'id' => 0 ),
				array( 'id' => 1 ),
				array( 'id' => 2 ),
			);
		}
	}
```

Before you dispatch URLs, router object calls the `expand` method of `ResourceHandler` class, which
generates RESTful routes into the routeset of router object. And below is the generated URLs:

	GET /restful/blog        - get blog list
	GET /restful/blog/:id    - get one blog record
	POST /restful/blog       - create one blog record
	PUT /restful/blog/:id    - update one blog record
	DELETE /restful/blog/:id - delete one blog record

You can override the `expand` method to define your own style RESTful URLs.

Now you should be able to dispatch RESTful urls:

```php
<?php

	$_SERVER['REQUEST_METHOD'] = 'get';
	$r = $router->dispatch('/restful/blog/1');

	// returns {"success":true,"data":{"id":"1","title":"title"},"message":"Record 1 loaded."}
	$r();   

	$_SRVER['REQUEST_METHOD'] = 'get';
	$r = $router->dispatch('/restful/blog');

	// {"success":true,"data":[{"id":0},{"id":1},{"id":2}],"message":"Record find success."}
	$r();
```

## Customize Resource Handler

Here is how RESTful route generator works:

```php
<?php

	public function expand($routes, $h, $r)
	{
		$routes->add( "/$r(\.:format)" , array($h,'handleFind'), 
			array( 
				':get' => true , 
				':default' => array( 'format' => 'json' ) 
			));

		$routes->add( '/' . $r . '(\.:format)' , array($h,'handleCreate'), 
			array( 
				':post' => true, 
				':default' => array( 'format' => 'json' ) 
			));

		$routes->add( '/' . $r . '/:id(\.:format)' , array($h,'handleLoad'),
			array( 
				':get' => true, 
				':default' => array( 'format' => 'json' )
			));

		$routes->add( '/' . $r . '/:id(\.:format)' , array($h,'handleUpdate'),
			array( 
				':put' => true, 
				':default' => array( 'format' => 'json' ) 
			));

		$routes->add( '/' . $r . '/:id(\.:format)' , array($h,'handleDelete'),
			array( 
				':delete' => true, 
				':default' => array( 'format' => 'json' ) 
			));
	}
```

To define your own RESTful Resource Handler (Generator), you can simply inherit class from
`Roller\Plugin\RESTful\ResourceHandler`:

```php
<?php
use Roller\Plugin\RESTful\ResourceHandler;

class YourResourceHandler extends ResourceHandler {

	// define your own expand method
	function expand( $routes , $handlerClass, $resourceId ) {

	}

}
```

.htaccess File for Apache
-------------------------

    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-s
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ your_router.php/$1 [NC,L]

Install Extension
-----------------

    $ cd extension
    $ phpize 
    $ ./configure 
    $ make && make install

Add config to your php.ini:

    extension=roller.so

Hacking
-------
Get Onion and install it:

    $ curl -s http://install.onionphp.org/ | bash

Run onion to install depedencies:

    $ onion bundle

Now you should be able to run phpunit =)

    $ phpunit tests

