<?php
namespace Roller;
use Exception;
use ReflectionObject;
use ReflectionFunction;
use ReflectionClass;

class MatchedRoute
{
	public $route;

	function __construct($route)
	{
		$this->route = $route;
	}

    function run() {
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

			// which is a callback with class
			if( is_string($cb[0]) ) {
                $obj = $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
				$cb[0] = $obj;
			}

			$rm = $rc->getMethod($cb[1]);
			$rps = $rm->getParameters();
		}
		elseif( is_a($cb,'\Roller\Controller') ) {
			$ro = new ReflectionObject( $cb );
			$rm = $ro->getMethod('run');
			$rps = $rm->getParameters();
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

        // XXX: check parameter numbers here
        return call_user_func_array($cb, $arguments );

    }

	// evaluate route
	function __invoke()
	{
        return $this->run();
	}
}

