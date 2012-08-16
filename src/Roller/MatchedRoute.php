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
     * current dispatched path
     */
    public $path;

    /**
     * @param Roller\Router $router router object.
     * @param array $route route hash.
     * @param string $path route path.
     */
    public function __construct($router,$route,$path)
    {
        $this->router = $router;
        $this->route = $route;
        $this->path = $path;
    }



    /**
     * Create object with/from ReflectionClass
     *
     * @param string $class Class name (which is optional if ReflectionClass object is specified)
     * @param array $args arguments for controller constructor.
     * @param ReflectionClass $rc Reflection class of controller class
     * @return Roller\Controller
     */
    public function createObjectFromReflection($class = null,$args = null,$rc = null)
    {
        if( ! $rc ) {
            $rc = new ReflectionClass($class);
        }
        return $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
    }

    public function getCallbackParameters($callback)
    {
        if( is_array($callback) ) {
            list($object,$method) = $callback;
            $r = is_object($object) ? new ReflectionObject($object) : new ReflectionClass($object);
            return $r->getMethod($method)->getParameters();
        }
        elseif( is_a($callback,'Closure') ) {
            $rf = new ReflectionFunction( $callback );
            return $rf->getParameters();
        }
    }

    /**
     * Build callback array 
     * 
     * @param mixed $cb callback object, can be array(object,method) or a Controller object
     * @param array $args arguments for contructor.
     * @return ReflectionParameters
     */
    public function initCallback( & $cb, $args)
    {
        $rps = null;
        if( is_array($cb) ) {
            $rc = new ReflectionClass( $cb[0] );

            // if the first argument is a class name string, 
            // then create the controller object.
            if( is_string($cb[0]) ) {
                $cb[0] = $this->controller = $args ? $rc->newInstanceArgs($args) : $rc->newInstance();
            } else {
                $this->controller = $cb[0];
            }

            // check controller action method
            if( $this->controller && ! method_exists( $this->controller ,$cb[1]) ) {
                throw new RouteException('Method ' . 
                    get_class($this->controller) . "->{$cb[1]} does not exist.", $this->route );
            }

            return $rc->getMethod($cb[1])->getParameters();
        }
        elseif( is_a($cb,'Roller\Controller') ) {
            $rc = new ReflectionClass( $cb );
            $rm = $rc->getMethod('run');
            $rps = $rm->getParameters();

            $this->controller = $this->createObjectFromReflection( $cb , $args , $rc );

            // rebuild callback array
            $cb = array( $this->controller, 'run');
            return $rps;
        }
        elseif( is_a($cb,'Closure') ) {
            $rf = new ReflectionFunction( $cb );
            return $rf->getParameters();
        }
        else {
            throw new Exception('Unsupported callback type');
        }
    }

    /**
     * Evaluate route and return response content.
     *
     * @return string
     */
    public function run() 
    {
        if( ($cb = $this->getCallback()) === null )
            throw new RouteException( 'callback attribute is not defined or empty.' , $this->route );

        /** constructor arguments **/
        $args = $this->route['args'];

        // validation action method prototype
        $vars = $this->getVars();

        // reflection parameters of the function or method
        $rps = $this->initCallback( $cb , $args );

        // check callback function
        if( ! is_callable($cb) )
            throw new RouteException( 'This route callback is not a valid callback.' , $this->route );

        // get relection method parameter prototype for checking...
        // and create arguments array
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
            else if( ! $param->isOptional() && ! $param->allowsNull ) {
                throw new RouteException( 'parameter is not defined.',  $this->route );
            }
        }

        if( $this->controller && is_a($this->controller,'Roller\Controller') ) {
            $this->controller->route = $this;
            $this->controller->router = $this->router;

            // runWrapper method runs: before, run, after, finalize method
            return $this->controller->runWrapper($cb,$arguments);
        } else {
            return call_user_func_array($cb, $arguments );
        }
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

    public function getPath()
    {
        return $this->path;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getRoute()
    {
        return $this->route;
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

