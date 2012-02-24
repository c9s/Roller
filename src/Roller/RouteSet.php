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
        $default     = $optinos[':default'];
        $route = RouteCompiler::compile(array(
            'pattern' => $path,
            'requirement' => $requirement,
            'default' => $default,
        ));
        $this->routes[] = $route;
    }

    public function mount( RouteSet $routes )
    {

    }

    public function current() 
    {
        $this->routes[ $this->i ];
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

}

