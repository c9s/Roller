<?php
namespace Roller\Plugin\RESTful;

abstract class BaseHandler
{
	public $message;

	public $data;


    /**
     * HTTP Status Code:
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @link http://restpatterns.org/HTTP_Status_Codes
     *
     * REST Pattern
     * @link http://restpatterns.org/
     */

    public function codeOk() 
    {
        header("HTTP/1.1 200 OK");
    }

    public function codeCreated()
    {
        header('HTTP/1.1 201 Created');
    }

    public function codeAccepted()
    {
        header('HTTP/1.1 202 Accepted');
    }

    public function codeNoContent() 
    {
        header('HTTP/1.1 204 No Content');
    }

    public function codeBadRequest()
    {
        header('HTTP/1.1 400 Bad Request');
    }

    public function codeForbidden()
    {
        header('HTTP/1.1 403 Forbidden');
    }

    public function codeNotFound()
    {
        header('HTTP/1.1 404 Not Found');
    }

    public function returnError($format,$message = null)
    {
        $this->codeBadRequest($message);
        return $this->renderFormat(array( 
            'success' => false,
            'errors' => $message,
        ), $format);
    }

	public function returnSuccess($format,$message = null)
	{
		return $this->renderFormat( 
			array( 
				'success' => true,
				'data' => $this->data,
				'message' => $this->message ?: $message,
			), $format );
	}

	public function renderFormat($data, $format)
	{
		switch($format) {
			case 'json':
                @header('content-type: application/json; charset=utf8;');
				return json_encode( $data );
			break;
            case 'yml':
			case 'yaml':
                @header('content-type: text/yaml; charset=utf8;');
				return yaml_emit( $data );
            case 'xml':
                @header('content-type: text/xml; charset=utf8;');
                $ser = new \SerializerKit\XmlSerializer;
                return $ser->encode( $data );
			break;
		}
	}

	public function getClass()
	{
		return get_class($this);
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

}

