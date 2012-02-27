<?php

use Roller\Plugin\RESTful\ResourceHandlerInterface;

class BlogResourceHandler implements ResourceHandlerInterface 
{
    public function create()    {  }
    public function update($id) {  }
    public function delete($id) {  }
    public function load($id)   {  }
    public function find()      {  }
}

class RESTfulTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $router = new Roller\Router;
        $restful = new Roller\Plugin\RESTful(array( 'prefix' => '/restful' ));
        $restful->registerResource( 'blog' , 'BlogResourceHandler' );
        $router->addPlugin($restful);

        $_SERVER['REQUEST_METHOD'] = 'get';
        $router->dispatch('/restful/blog');
    }
}

