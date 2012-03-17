<?php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Roller\Annotations\Route;

class AnnotationTestController {

    /**
     * @Route("/hello/:name", name="_hello", requirements={"name" = ".+"}, vars={ "k" = 123, "b" = 234 })
     */
    function helloAction() { 

    }

}

class ClassReaderTest extends PHPUnit_Framework_TestCase
{


    function test()
    {
        AnnotationRegistry::registerAutoloadNamespace('Roller\Annotations\\', 'src');

        // AnnotationRegistry::loadAnnotationClass('Roller\Annotations\Route');
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();

        $reflClass = new ReflectionClass('AnnotationTestController');
        $reflMethod = $reflClass->getMethod('helloAction');
        // $classAnnotations = $reader->getClassAnnotations($reflClass);
        $methodAnnotations = $reader->getMethodAnnotations($reflMethod);

        foreach ($methodAnnotations as $annot) {
            var_dump( $annot ); 
        }


        
    }
}

