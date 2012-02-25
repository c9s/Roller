RESTful plugin plan
===================

    $restful = new Roller\Plugin\RESTful( array(
        'prefix' => '/restful',
        'resource_id' => array('post','blog','comment'),
    ));

    $restful->urlGenerator = function($resourceId) { 
        $routes = new RouteSet;
        $routes->add( '/:resource_id/new(.:format)' , .... );
        $routes->add( '/:resource_id(.:format)' , .... );
        $routes->add( '/:resource_id/:id(.:format)' , .... , [ ':method' => 'post' ] );
        $routes->add( '/:resource_id/:id(.:format)' , .... , [ ':method' => 'put' ] );
        return $routes;
    };

    $restful->createHandler = function($resourceId) {

    };

    $restful->updateHandler = function($resourceId,$id) {

    };

    $restful->deleteHandler = function($resourceId,$id) {

    };

    $restful->mount = function() {
        $method = $_SERVER['REQUEST_METHOD'];
    };

    $router->addPlugin( $restful );


