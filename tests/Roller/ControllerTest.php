<?php

class HelloWorldController extends Roller\Controller
{
    function indexAction($name) {
        return 'Hello ' . $name;
    }
}

class ControllerTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $controller = new HelloWorldController;
        ok($controller);
        $response = $controller->runWrapper(array($controller,'indexAction'),array( 
            'name' => 'John'
        ));
        is( 'Hello John', $response );
    }
}

