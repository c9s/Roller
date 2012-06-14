<?php

namespace Roller;

abstract class Controller
{


    /**
     * @var Roller\MatchedRoute object
     */
    public $route;

    /**
     * @var Roller\Router object
     */
    public $router;



    public function __construct()
    {
        $this->init();
    }


    public function init() { }

    public function before() { }

    public function after() { }

    public function run() { }

    public function finalize() { }

    public function runWrapper($callable,$arguments)
    {
        $this->before();
        $response = call_user_func_array($callable,$arguments);
        $this->after();
        $this->finalize();
        return $response;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getRouter()
    {
        return $this->router;
    }



    public function parseInput()
    {
        static $params;
        $params = array();
        parse_str( $this->readInput() , $params );
        return $params;
    }

    public function readInput()
    {
        return file_get_contents('php://input');
    }

    public function renderJson($data)
    {
        return $this->renderFormat($data,'json');
    }

    public function renderYaml($data)
    {
        return $this->renderFormat($data,'yaml');
    }

    public function renderXml($data)
    {
        return $this->renderFormat($data,'xml');
    }

    public function renderFormat($data, $format)
    {
        switch($format) {
            case 'json':
                @header('content-type: application/json; charset=utf-8;');
                return json_encode( $data );
            break;
            case 'yml':
            case 'yaml':
                @header('content-type: text/yaml; charset=utf-8;');
                return yaml_emit( $data );
            case 'xml':
                @header('content-type: text/xml; charset=utf-8;');
                $ser = new \SerializerKit\XmlSerializer;
                return $ser->encode( $data );
            break;
        }
    }

}

