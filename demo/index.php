<?php
require '../vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$loader = new \Universal\ClassLoader\BasePathClassLoader( array('../src','../vendor/pear'));
$loader->useIncludePath(true);
$loader->register();


use Roller\Plugin\RESTful\ResourceHandler;
use Roller\Plugin\RESTful\GenericHandler;

class MyGenericHandler extends GenericHandler
{
    public function create($resource) { 
        return array( 'id' => 99 );
    }

    public function load($resource,$id) { 
        return array( 'id' => $id );
    }

    public function update($resource,$id) { 
        return array( 'id' => $id );
    }

    public function delete($resource,$id) { 
        return array( 'id' => $id );
    }

    public function find($resource) { 
        return range(1,10);
    }

}

$router = new Roller\Router( null, array( 
    // 'cache_id' => 'router_demo'
));

$restful = new Roller\Plugin\RESTful(array( 'prefix' => '/=' ));
$restful->setGenericHandler( 'MyGenericHandler' );
$router->addPlugin($restful);

foreach( range(1,100) as $i ) 
    $router->add("/foo$i" , function() {  return 'true'; });

$router->add('/',function() { 
    return 'Hello World, please request /=/test for RESTful resource handler demo.';
});

$r = $router->dispatch( isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/' );
#  var_dump( $_SERVER['PATH_INFO'] ); 
#  var_dump( $r['compiled'] ); 
#  var_dump( $r['vars'] ); 

if( $r )
    echo $r();
