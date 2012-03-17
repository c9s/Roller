<?php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Roller\Annotations\Route;

class AnnotationTestController {

    /**
     * @Route("/hello/:name", name="_hello", requirements={"name" = ".+"}, vars={ "k" = 123, "b" = 234 })
     */
    function helloAction($name) { 
        return $name;
    }

    /**
     * @Route("/")
     */
    function indexAction() {
        return 'index';
    }

}

class ClassReaderTest extends PHPUnit_Framework_TestCase
{


    function test()
    {
        $router = new Roller\Router;
        $router->importAnnotationMethods( 'AnnotationTestController' , '/Action$/' );
        $route = $router->dispatch('/');
        ok( $route );
        is( 'index' , $route() );

        $route = $router->dispatch('/hello/John');
        ok( $route );
        is( 'John' , $route() );
    }
}

