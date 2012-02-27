<?php

namespace Roller\Plugin\RESTful;

abstract class ResourceHandler
{

	public $message;

	public $data;


	/**
	 * retrieve record list
	 */
	abstract public function find();


	/**
	 * create new record
	 */
	abstract public function create();


	/**
	 * delete record
	 */
	abstract public function delete($id);


	/**
	 * update record 
	 */
	abstract public function update($id);

	/**
	 * load one record
	 */
	abstract public function load($id);



	/**
	 * expand resource handlers to routeset 
	 */
	public function expand()
	{
		// xxx
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

	public function handleFind($format)
	{
		$this->data = $this->find();
		return $this->returnSuccess($format, "Record find success.");
	}

	public function handleCreate($format) 
	{
		$this->data = $this->create();
		return $this->returnSuccess($format, "Record $id created.");
	}

	public function handleUpdate($id,$format)
	{
		$this->data = $this->update($id);
		return $this->returnSuccess($format, "Record $id updated.");
	}

	public function handleLoad($id,$format)
	{
		$this->data = $this->load($id);
		return $this->returnSuccess($format, "Record $id loaded.");
	}

	public function handleDelete($id,$format)
	{
		$this->data = $this->load($id);
		return $this->returnSuccess($format, "Record $id deleted.");
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

}


