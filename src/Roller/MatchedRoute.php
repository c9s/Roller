<?php
namespace Roller;
use Exception;
use ReflectionObject;
use ReflectionFunction;
use ReflectionClass;

class MatchedRoute
{
	public $route;
    public $router;

	public function __construct($router,$route)
	{
        $this->router = $router;
		$this->route = $route;
	}

    public function run() 
    {
		$cb = $this->route['callback'];
        $controller = null;

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
                $controller = $obj;
                $cb[0] = $obj;
            }
            $rm = $rc->getMethod($cb[1]);
            $rps = $rm->getParameters();
		}
		elseif( is_a($cb,'\Roller\Controller') ) {
			$rc = new ReflectionClass( $cb );
			$rm = $rc->getMethod('run');
			$rps = $rm->getParameters();

            $obj = $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
            $controller = $obj;
            $cb = array( $obj, 'run');
            $controller = $obj;
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

        if( is_a($controller,'Roller\Controller') ) {
            $controller->route = $this->route;
            call_user_func( array($controller,'before') );
        }

        $ret = call_user_func_array($cb, $arguments );

        if( is_a($controller,'Roller\Controller') ) {
            call_user_func( array($controller,'after') );
        }
        return $ret;
    }

    function getRequirement()
    {
        if( isset($this->route['requirement']) )
            return $this->route['requirement'];
    }

    function getDefault()
    {
        if( isset($this->route['default'] ) ) 
            return $this->route['default'];
    }

    public function getVars()
    {
        if( isset($this->route['vars']) )
            return $this->route['vars'];
    }

	// evaluate route
	function __invoke()
	{
        return $this->run();
	}
}

