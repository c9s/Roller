<?php
namespace Roller\Plugin\RESTful;

abstract class BaseHandler
{
	public $message;

	public $data;

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
				return json_encode( $data );
			break;

			case 'yaml':
				return yaml_emit( $data );
			break;
		}
	}

	public function getClass()
	{
		return get_class($this);
	}

}

