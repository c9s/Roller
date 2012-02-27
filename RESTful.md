RESTful plugin plan
===================

Initalize RESTful Plugin

    $restful = new Roller\Plugin\RESTful( array(
        'prefix' => '/restful',
    ));
    $r->setValidResources(array( 'blogs' , 'posts' ));

Setup generic resource handler:

    $r->registerHandler('YourGenericHandler');

Add resource handler for specified resource:

    $r->registerResource( 'posts', 'PostRESTfulHandler' );

RESTfulGenericHandler:

    class RESTfulGenericHandler {

        // a wrapper for create action
        function resourceCreate($resource) {

            // read data , parse data

            $data = $this->create( );
            return $this->renderFormat($data, 'json');
        }

        function resourceDelete($resource) 
        {

        }

    }

To implement your generic resource handler (for all resources):

    class GenericHandler extends RESTfulGenericHandler
    {

        function create($resourceId) {

        }

        function update($resourceId) {

        }

        function delete($resourceId) {

        }

        function list($resourceId) {

            return array( ..... );
        }

    }

Then implement your reousrce RESTfulHandler

    class YourRESTfulHandler implements RESTfulHandlerInterface
    {

        function create() {

        }

        function update() {

        }

        function delete() {

        }

        function listAll() {

        }

        function list() {

        }

        function load() {

        }

    }



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


