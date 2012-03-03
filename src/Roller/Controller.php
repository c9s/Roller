<?php

namespace Roller;

abstract class Controller
{


    /**
     * @var Roller\MatchedRoute object
     */
    public $route;

    /**
     * @var Roller\Router object
     */
    public $router;



    public function __construct()
    {
        $this->init();
    }

    public function init()
    {

    }

    public function before()
    {

    }

    public function after() 
    {

    }


    public function getRoute()
    {
        return $this->route;
    }

    public function getRouter()
    {
        return $this->router;
    }

}

