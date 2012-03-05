<?php
namespace Roller;
use Iterator;
use Roller\RouteCompiler;
use Exception;

class RouteSet implements Iterator
{
    public $routesMap = array();

    public $routes = array();

    public $i = 0;

    public function __call($m,$a) {
        switch( $m ) {
        case 'post':
        case 'head':
        case 'get':
            $path = $a[0];
            $callback = $a[1];
            $options = isset($a[2]) ? $a[2] : array();
            $options[':method'] = $m;
            return $this->add( $path , $callback , $options );
            break;
        }
        throw new Exception("Method $m not found.");
    }



    /**
     * __call magic is always slow than methods 
     */
    public function get($path, $callback, $options = array() )
    {
        $options[':method'] = 'get';
        return $this->add( $path,  $callback , $options );
    }

    public function post($path, $callback, $options = array() )
    {
        $options[':method'] = 'post';
        return $this->add( $path, $callback, $options );
    }



    private function buildRoute($path,$callback,$options = array() )
    {
        $route = array(
            'path' => null,
            'args' => null,
        );
        $route['path']        = $path;

        if( is_string($callback) && false !== strpos($callback,':') ) {
            $callback = explode(':',$callback);
        }
        $route['callback']    = $callback;




        $requirement = array();
        if( isset($options[':requirement']) ) {
            $requirement = $options[':requirement'];
        } else {
            foreach( $options as $k => $v ) {
                if( $k[0] !== ':' ) {
                    $requirement[ $k ] = $v;
                }
            }
        }
        $route['requirement'] = $requirement;


        /* :secure option for https */
        if( isset($options[':secure']) ) {
            $route['secure'] = true;
        }

        if( isset($options[':default']) ) {
            $route['default'] = $options[':default'];
        }

        if( isset($options[':method']) ) {
            $route['method'] = $options[':method'];
        } elseif( isset($options[':post']) ) {
            $route['method'] = 'post';
        } elseif( isset($options[':get']) ) {
            $route['method'] = 'get';
        } elseif( isset($options[':head']) ) {
            $route['method'] = 'head';
        } elseif( isset($options[':put']) ) {
            $route['method'] = 'put';
        } elseif( isset($options[':delete']) ) {
            $route['method'] = 'delete';
        }

        /** 
         * arguments pass to constructor 
         */
        if( isset($options[':args']) ) {
            $route['args'] = $options[':args'];
        }

        // always have a name
        if( isset($options[':name']) ) {
            $route['name'] = $options[':name'];
        } else {
            $route['name'] = preg_replace( '/\W/' , '_' , $route['path'] );
        }
        return $route;
    }

    /** 
     *
     * @param string $path
     * @param mixed $callback
     * @param array $options
     */
    public function add($path, $callback, $options = array() )
    {
        // xxx: write this in extension 
        $route = $this->buildRoute( $path ,$callback, $options );
        return $this->routes[] = $this->routesMap[ $route['name'] ] = & $route;
    }




    /**
     * find route by route path
     *
     */
    public function findRouteByPath( $path ) 
    {
        foreach( $this->routes as $route ) {
            if( isset($route['path']) && $route['path'] == $path ) {
                return $route;
            }
        }
    }


    /**
     * get route by route name
     */
    public function getRoute($name) 
    {
        if( isset($this->routesMap[ $name ] ) ) {
            return $this->routesMap[ $name ];
        }
    }


    // xxx: write this in extension.
    public function mount( $prefix, RouteSet $routes )
    {
        foreach( $routes as $r ) {
            $r['path'] = $prefix . rtrim($r['path'],'/');
            $this->routes[] = $r;
        }
    }


    // xxx: write this in extension to improve compile time performance.
    public function compile()
    {
        foreach( $this->routes as &$r ) {
            $r = RouteCompiler::compile($r);
        }
    }


    /** interface for iterating **/
    public function current() 
    {
        return $this->routes[ $this->i ];
    }

    public function key () {
        return $this->i;
    }

    public function next () {
        ++$this->i;
    }

    public function rewind () {
        $this->i = 0;
    }

    public function valid () {
        return isset( $this->routes[ $this->i ] );
    }


    /** interface for loading cache from source */
    static function __set_state($data)
    {
        $a = new self;
        $a->routes = $data['routes'];
        $a->i = $data['i'];
        return $a;
    }


}

