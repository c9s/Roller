<?php
if( extension_loaded('roller') ) {
    echo "extension loaded\n";
}

require '../vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$loader = new \Universal\ClassLoader\BasePathClassLoader( array('../src','../vendor/pear', '/opt/local/lib/php'));
$loader->useIncludePath(true);
$loader->register();

echo "init router\n";
$router = new Roller\Router;
$router->add('/',function() { 
    return 'Hello World, please request /=/test for RESTful resource handler demo.';
});


$router->add('/blog/:year' , function() { return 'bar'; } ,array(
    ':default' => array(
        'year' => 1000
    )
));

#  $i = 0;
#  foreach( range(1,1000) as $i ) {
#      $router->add('/foo' . $i , function() { return 'bar'; });
#  }


$router->routes->compile();
// var_dump( $router->routes->routes );

echo "dispatching\n";

$_SERVER['REQUEST_METHOD'] = 'GET';

/*
echo "before_dispatch: " . (memory_get_usage() / 1024 / 1024) . " MB\n";
$regs = null;
$route = roller_dispatch( $router->routes->routes , "/foo$i" , $regs );
echo "after_dispatch: " . (memory_get_usage() / 1024 / 1024) . " MB\n";
var_dump( $route, $regs ); 
 */




echo "===> testing variables\n";
$regs = null;
$route = roller_dispatch( $router->routes->routes , "/blog/2012" , $regs );
var_dump( $route ); 
var_dump( isset($route['vars']['year']) );
var_dump( $route['vars']['year'] == 2012 );


echo "===> testing default variables\n";
$regs = null;
$route = roller_dispatch( $router->routes->routes , "/blog" );
var_dump( $route ); 

echo "===> testing default variables\n";
$route = roller_dispatch( $router->routes->routes , "/blog" );
var_dump( $route ); 
