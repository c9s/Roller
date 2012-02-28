<?php
if( extension_loaded('roller') ) {
    echo "extension loaded\n";
}


#  $i = 0;
#  foreach( range(1,1000) as $i ) {
#      $router->add('/foo' . $i , function() { return 'bar'; });
#  }


// var_dump( $router->routes->routes );

echo "dispatching\n";


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
