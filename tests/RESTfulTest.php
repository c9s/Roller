<?php

use Roller\Plugin\RESTful\ResourceHandler;

class BlogResourceHandler extends ResourceHandler
{
    public function create()    { 
        return array( 'id' => 1 );
    }

    public function update($id) { 
        return array( 'id' => 1 );
    }

    public function delete($id) { 
        return array( 'id' => 'delete' );
    }

    public function load($id)   { 
        return array( 'id' => $id , 'title' => 'title' );
    }

    public function find()      { 
        return array( 
            array( 'id' => 0 ),
            array( 'id' => 1 ),
            array( 'id' => 2 ),
        );
    }
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
        $r = $router->dispatch('/restful/blog/1');
        is( '{"success":true,"data":{"id":"1","title":"title"},"message":"Record 1 loaded."}' , $r() );

        $_SRVER['REQUEST_METHOD'] = 'get';
        $r = $router->dispatch('/restful/blog');
        is( '{"success":true,"data":[{"id":0},{"id":1},{"id":2}],"message":"Record find success."}' , $r() );
    }
}

