<?php
namespace Roller;
use Iterator;
use Roller\RouteCompiler;
use Exception;

class RouteSet implements Iterator
{
    public $routes = array();
    public $i = 0;

    public function __construct()
    {

    }

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




    public function add($path, $callback, $options = array() )
    {
        if( is_string($callback) ) {
            $callback = explode(':',$callback);
        }

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

        $default = array();
        if( isset($options[':default']) )
            $default = $options[':default'];

        $method = null; /* any method */
        if( isset($options[':method']) ) {
            $method = $options[':method'];
        } elseif( isset($options[':post']) ) {
            $method = 'post';
        } elseif( isset($options[':get']) ) {
            $method = 'get';
        } elseif( isset($options[':head']) ) {
            $method = 'head';
        } elseif( isset($options[':put']) ) {
            $method = 'put';
        } elseif( isset($options[':delete']) ) {
            $method = 'delete';
        }

        /** 
         * arguments pass to constructor 
         */
        $args = null;
        if( isset($options[':args']) ) {
            $args = $options[':args'];
        }

        return $this->routes[] = array(
            'path' => $path,
            'requirement' => $requirement,
            'default' => $default,
            'callback' => $callback,
            'method' => $method,
            'args' => $args,
        );
    }

    public function mount( $prefix, RouteSet $routes )
    {
        foreach( $routes as $r ) {
            $r['path'] = $prefix . $r['path'];
            $this->routes[] = $r;
        }
    }

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

