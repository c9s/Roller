<?php

namespace Roller;

abstract class Controller
{
    public $route;
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
}

