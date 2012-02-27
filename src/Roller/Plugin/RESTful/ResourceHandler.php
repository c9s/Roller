<?php

namespace Roller\Plugin\RESTful;

abstract class ResourceHandler
{


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
		$data = $this->find();
		return $this->renderFormat( $data, $format );
	}

	public function handleCreate($format) 
	{
		$data = $this->create();
		return $this->renderFormat( $data, $format );
	}

	public function handleUpdate($id,$format)
	{
		$data = $this->update($id);
		return $this->renderFormat( $data, $format );
	}

	public function handleLoad($id,$format)
	{
		$data = $this->load($id);
		return $this->renderFormat( $data, $format );
	}

	public function handleDelete($id,$format)
	{
		$data = $this->load($id);
		return $this->renderFormat( $data, $format );
	}
	
}




