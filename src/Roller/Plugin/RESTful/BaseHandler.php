<?php
namespace Roller\Plugin\RESTful;




abstract class BaseHandler
{
	public $message;

	public $data;


    /**
     * HTTP Status Code:
     *
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * http://restpatterns.org/HTTP_Status_Codes
     */
    public function codeCreated($message = 'Created')
    {
        header("HTTP/1.1 201 $message");
    }

    public function codeOk($message = 'OK') 
    {
        header("HTTP/1.1 200 $message");
    }

    public function codeBadRequest($message = 'Bad Request')
    {
        header("HTTP/1.1 400 $message");
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
                header('content-type: application/json; charset=utf8;');
				return json_encode( $data );
			break;
			case 'yaml':
                header('content-type: application/yaml; charset=utf8;');
				return yaml_emit( $data );
			break;
		}
	}

	public function getClass()
	{
		return get_class($this);
	}

}

