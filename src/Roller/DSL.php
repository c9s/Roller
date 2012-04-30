<?php

function router()
{
    static $router;
    if( $router )
        return $router;
    $router = new Roller\Router;
    return $router;
}

function on() {
    $router = router();
    $args = func_get_args();
    if( 2 === count($args) ) {
        list($path,$callback) = $args;
        $router->add($path ,$callback);
    }
    elseif( 3 === count($args) ) {
        list($path,$options,$callback) = $args;
        $router->add($path, $callback, $options );
    }
    else {
        die('Router: Invalid arguments');
    }
}



function dispatch($path)
{
    $router = router();
    return $router->dispatch($path);
}

