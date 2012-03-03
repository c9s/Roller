<?php
namespace Roller;
use Exception;
use ReflectionObject;
use ReflectionFunction;
use ReflectionClass;
use Roller\Exception\RouteException;
use ArrayAccess;



/**
 *
 * ArrayAccess supports:
 *
 *  $route;
 *  $route['path'];
 *  $route['compiled'];
 *
 */

class MatchedRoute
    implements ArrayAccess
{
    /**
     * Route data array()
     */
	public $route;


    /**
     * @var Roller\Router router object
     */
    public $router;

    /**
     * controller object (if route call class and object to handle request)
     *
     * @var Controller object
     */
    public $controller;

    /**
     * @param Roller\Router $router router object.
     * @param array $route route hash.
     */
	public function __construct($router,$route)
	{
        $this->router = $router;
		$this->route = $route;
	}


    public function createController($rc,$args = null) 
    {
        return $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
    }

    public function initCallback( & $cb, $args)
    {
        $rps = null;
		if( is_array($cb) ) {
            $rc = new ReflectionClass( $cb[0] );
            if( is_string($cb[0]) ) {
                $obj = $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
                $this->controller = $obj;
                $cb[0] = $obj;
            }
            else {
                $this->controller = $cb[0];
            }

            if( $this->controller && ! method_exists( $this->controller ,$cb[1]) ) {
                throw new RouteException("Method " . 
                    get_class($this->controller) . "->{$cb[1]} does not exist.", $this->route );
            }

            $rm = $rc->getMethod($cb[1]);
            $rps = $rm->getParameters();
		}
		elseif( is_a($cb,'Roller\Controller') ) {
			$rc = new ReflectionClass( $cb );
			$rm = $rc->getMethod('run');
			$rps = $rm->getParameters();

            $this->controller = $this->createController( $rc, $args );
            $cb = array( $controller, 'run');
		}
		elseif( is_a($cb,'Roller\Controller') ) {
			$rc = new ReflectionClass( $cb );
			$rm = $rc->getMethod('run');
			$rps = $rm->getParameters();

            $this->controller = $this->createController( $rc, $args );
            $cb = array( $controller, 'run');
		}
		elseif( is_a($cb,'Closure') ) {
			$rf = new ReflectionFunction( $cb );
			$rps = $rf->getParameters();
		}
        return $rps;
    }

    /**
     * To evaluate route content
     */
    public function run() 
    {
        if( ($cb = $this->getCallback()) === null )
            throw new RouteException( 'callback attribute is not defined or empty.' , $this->route );

        /** constructor arguments **/
        $args = $this->route['args'];

        // validation action method prototype
        $vars = $this->getVars();

		// reflection parameters
		$rps = $this->initCallback( $cb , $args );

        // check callback function
		if( ! is_callable($cb) )
			throw new RouteException( 'This route callback is not a valid callback.' , $this->route );

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
                throw new RouteException( 'parameter is not defined.',  $this->route );
            }
        }
        if( $this->controller && is_a($this->controller,'Roller\Controller') ) {
            $this->controller->route = $this->route;
            $this->controller->router = $this->router;
            $this->controller->before();
        }

        $ret = call_user_func_array($cb, $arguments );

        if( $this->controller && is_a($this->controller,'Roller\Controller') ) {
            $this->controller->after();
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

    public function getCallback()
    {
        if( isset($this->route['callback']) )
            return $this->route['callback'];
    }

    public function getVars()
    {
        if( isset($this->route['vars']) )
            return $this->route['vars'];
    }

    public function getArgs()
    {
        if( isset($this->route['args']) )
            return $this->route['args'];
    }

    public function getController()
    {
        return $this->controller;
    }



    /** magic accessor interface **/
    public function __isset($n) {
        return isset($this->route[$n]);
    }

    public function __get($n) {
        return ( isset($this->route[ $n ] ) ) ? $this->route[ $n ] : null;
    }

    public function __set($n,$v) {
        $this->route[ $n ] = $v;
    }


    /** ArrayAccess interface **/
    public function offsetSet($name,$value)
    {
        $this->route[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->route[ $name ]);
    }
    
    public function offsetGet($name)
    {
        return $this->route[ $name ];
    }
    
    public function offsetUnset($name)
    {
        unset($this->route[$name]);
    }
    

	// evaluate route
	function __invoke()
	{
        return $this->run();
	}
}

