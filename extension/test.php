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

foreach( range(1,1000) as $i ) {
    $router->add('/foo' . $i , function() { return 'bar'; });
}

$router->routes->compile();
// var_dump( $router->routes->routes );

echo "dispatching\n";
$regs = null;
$route = roller_dispatch( $router->routes->routes , '/foo1000' , $regs );
var_dump( $route, $regs ); 
