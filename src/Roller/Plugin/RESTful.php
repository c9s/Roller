<?php
namespace Roller\Plugin;
use Roller\RouteSet;
use Roller\Router;
use Roller\PluginInterface;

/*

Notes:

    Create = POST
    Retrieve = GET
    Update = PUT
    Delete = DELETE


    Define a way to specify handler,
*/
class RESTful implements PluginInterface
{

    /* can define mixin methods here */


    /**
     * @var array resource handlers
     */
    public $resources = array();

    /**
     * is used for generic resource handler.
     *
     * @var array valid resource id list
     */
    public $validResources = array();


    public $genericHandler;


    /**
     * route prefix
     */
    public $prefix;

    public function __construct($options = array() )
    {
        if( isset($options['prefix']) )
            $this->prefix = $options['prefix'];

    }

    public function setValidResources($resources)
    {
        $this->validResources = $resources;
    }

    public function addValidResource($resourceId)
    {
        $this->validResources[] = $resourceId;
    }

    public function registerResource( $resourceId, $handlerClass )
    {
        $this->resources[ $resourceId ] = $handlerClass;
    }

    public function setGenericHandler( $genericHandlerClass )
    {
        $this->genericHandler = $genericHandlerClass;
    }


    public function beforeCompile($router)
    {
        // compile and register routes here.
        $routes = new \Roller\RouteSet;

        // Retrieve All => GET /restful/posts
        // Retrieve     => GET /restful/posts/:id
        // Create       => POST /restful/posts
        // Update       => PUT  /restful/posts
        // Delete       => DELETE /restful/posts/:id
        foreach( $this->resources as $r => $h ) {

            $routes->add( "/$r(\.:format)" , array($h,'find'), 
                    array( ':get' => true , 'format' => 'json' ) );

            $routes->add( '/' . $r . '(\.:format)' , array($h,'create'), 
                    array( ':post' => true, 'format' => 'json' ) );

            $routes->add( '/' . $r . '/:id(\.:format)' , array($h,'load'),
                    array( ':get' => true, 'format' => 'json' ) );

            $routes->add( '/' . $r . '/:id(\.:format)' , array($h,'update'),
                    array( ':put' => true, 'format' => 'json' ) );

            $routes->add( '/' . $r . '/:id(\.:format)' , array($h,'delete'),
                    array( ':delete' => true, 'format' => 'json' ) );
        }
		$router->mount( $this->prefix , $routes );
    }

    public function afterCompile($router)
    {
        // 
    }

}

