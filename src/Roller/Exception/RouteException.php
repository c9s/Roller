<?php
namespace Roller\Exception;
use Exception;

class RouteException extends Exception
{
    public $route;

    function __construct( $message , $route )
    {
        $this->route = $route;
        parent::__construct( $message );
    }

    function getRoute()
    {
        return $this->route;
    }
}


