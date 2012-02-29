<?php
namespace Roller;
use Iterator;
use Roller\RouteCompiler;

class RouteSet implements Iterator
{
    public $routes = array();
    public $i = 0;

    public function __construct()
    {

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
        if( isset($options[':method']) )
            $method = $options[':method'];
        elseif( isset($options[':post']) )
            $method = 'post';
        elseif( isset($options[':get']) )
            $method = 'get';
        elseif( isset($options[':head']) )
            $method = 'head';
        elseif( isset($options[':put']) )
            $method = 'put';
        elseif( isset($options[':delete']) )
            $method = 'delete';

        return $this->routes[] = array(
            'path' => $path,
            'requirement' => $requirement,
            'default' => $default,
            'callback' => $callback,
            'method' => $method,
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

    static function __set_state($data)
    {
        $a = new self;
        $a->routes = $data['routes'];
        $a->i = $data['i'];
        return $a;
    }

}

