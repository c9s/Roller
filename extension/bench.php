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



$b = new SimpleBench;
$b->setN(10);

$b->iterate('roller_ext', 'roller_ext' , function() use ($router) {
    $regs = null;
    $r = roller_dispatch( $router->routes->routes , '/foo1000' , $regs );
});

$b->iterate('roller' , 'roller' , function() use ($router) {
    $r = $router->dispatch('/foo1000');
});

// var_dump( $r , $regs ); 
// var_dump( hello_array_value( array(  'foo' => 'what' ) , 'foo' ) );

$result = $b->compare();
echo $result->output('Console');
// $result->output('EzcGraph');
