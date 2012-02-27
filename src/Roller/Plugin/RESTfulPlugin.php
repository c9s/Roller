<?php
namespace Roller\Plugin;

use Roller\PluginInterface;

/*


Notes:

    Create = POST
    Retrieve = GET
    Update = PUT
    Delete = DELETE

    Create => POST /restful/posts
    Retrieve => GET /restful/posts/:id
    Update => PUT /restful/posts
    Delete => DELETE /restful/delete

    Define a way to specify handler,
*/
class RESTfulPlugin implements PluginInterface
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


    public setValidResources($resources)
    {
        $this->validResources = $resources;
    }

    public addValidResource($resourceId)
    {
        $this->validResources[] = $resourceId;
    }

    public registerResource( $resourceId, $handlerClass )
    {
        $this->resources[ $resourceId ] = $handlerClass;
    }

    public setGenericHandler( $genericHandlerClass )
    {
        $this->genericHandler = $genericHandlerClass;
    }


    public function beforeCompile($router)
    {
        // compile and register routes here.


    }

    public function afterCompile($router)
    {
        // 
    }

}

