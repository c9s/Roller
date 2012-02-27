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
     * resource handlers
     */
    public $resources = array();

    /**
     * @var array valid resource id list
     */
    public $validResources = array();





}

