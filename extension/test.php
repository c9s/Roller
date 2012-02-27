<?php
if( extension_loaded('roller') ) {
    echo "extension loaded\n";
}

require '../vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$loader = new \Universal\ClassLoader\BasePathClassLoader( array('../src','../vendor/pear'));
$loader->useIncludePath(true);
$loader->register();

echo "init router\n";
$router = new Roller\Router;
$router->add('/',function() { 
    return 'Hello World, please request /=/test for RESTful resource handler demo.';
});

foreach( range(1,10000) as $i ) {
    $router->add('/foo' . $i , function() { return 'bar'; });
}

$router->routes->compile();
// var_dump( $router->routes->routes );

echo "dispatching\n";

$regs = null;
$r = roller_dispatch( $router->routes->routes , '/foo10000' , $regs );

var_dump( $r , $regs ); 

// var_dump( hello_array_value( array(  'foo' => 'what' ) , 'foo' ) );
