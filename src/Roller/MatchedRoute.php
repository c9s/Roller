<?php
namespace Roller;
use Exception;
use ReflectionObject;
use ReflectionFunction;
use ReflectionClass;

class MatchedRoute
{
    /**
     * route data array()
     */
	public $route;


    public $router;

    /**
     * controller object (if route call class and object to handle request)
     */
    public $controller;

	public function __construct($router,$route)
	{
        $this->router = $router;
		$this->route = $route;
	}

    public function run() 
    {
		$cb = $this->route['callback'];

		if( ! is_callable($cb) )
			throw new Exception( 'This route callback is not a valid callback.' );

        /** constructor arguments **/
        $args = $this->route['args'];

        // validation action method prototype
        $vars = isset($this->route['vars']) ? $this->route['vars'] : array();

		// reflection parameters
		$rps = null;
		if( is_array($cb) ) {
            $rc = new ReflectionClass( $cb[0] );
            if( is_string($cb[0]) ) {
                $obj = $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
                $this->controller = $obj;
                $cb[0] = $obj;
            }
            if( ! method_exists($obj,$cb[1]) )
                throw new Exception("Method {$cb[1]} does not exist.");
            $rm = $rc->getMethod($cb[1]);
            $rps = $rm->getParameters();
		}
		elseif( is_a($cb,'Roller\Controller') ) {
			$rc = new ReflectionClass( $cb );
			$rm = $rc->getMethod('run');
			$rps = $rm->getParameters();

            $obj = $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
            $this->controller = $obj;
            $cb = array( $obj, 'run');
            $this->controller = $obj;
		}
		elseif( is_a($cb,'Closure') ) {
			$rf = new ReflectionFunction( $cb );
			$rps = $rf->getParameters();
		}

        // get relection method parameter prototype for checking...
        $arguments = array();
        foreach( $rps as $param ) {
			$n = $param->getName();
            if( isset( $vars[ $n ] ) ) 
            {
                $arguments[] = $vars[ $n ];
            } 
            else if( isset($this->route['default'][ $n ] )
                            && $default = $this->route['default'][ $n ] )
            {
                $arguments[] = $default;
            }
            else {
                // throw new Exception("controller parameter error: ");
            }
        }

        if( $this->controller && is_a($this->controller,'Roller\Controller') ) {
            $this->controller->route = $this->route;
            $this->controller->router = $this->router;
            call_user_func( array($this->controller,'before') );
        }

        $ret = call_user_func_array($cb, $arguments );

        if( $this->controller && is_a($this->controller,'Roller\Controller') ) {
            call_user_func( array($this->controller,'after') );
        }
        return $ret;
    }

    public function getRequirement()
    {
        if( isset($this->route['requirement']) )
            return $this->route['requirement'];
    }

    public function getDefault()
    {
        if( isset($this->route['default'] ) ) 
            return $this->route['default'];
    }

    public function getVars()
    {
        if( isset($this->route['vars']) )
            return $this->route['vars'];
    }

    public function getController()
    {
        return $this->controller;
    }

	// evaluate route
	function __invoke()
	{
        return $this->run();
	}
}

