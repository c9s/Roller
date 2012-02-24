<?php
namespace Roller;

class Router
{
	public $routes;

	function __construct($routes = null)
	{
		$this->routes = $routes ?: new RouteSet;
	}

	function add($path,$callback,$options=array() )
	{
		return $this->routes->add( $path, $callback, $options );
	}

	function dispatch($path)
	{
		$this->routes->compile();
        foreach( $this->routes as $route ) {
            if( preg_match( $route['compiled'], $path, $regs ) ) {
                foreach( $route['variables'] as $k ) {
					if( isset($regs[$k]) ) {
						$route['vars'][ $k ] = $regs[$k];
					} else {
						$route['vars'][ $k ] = $route['default'][ $k ];
					}
                }
                return new MatchedRoute($route);
            }
        }
		return false;
	}

}
