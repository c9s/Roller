<?php

namespace Roller\Plugin\RESTful;

interface ResourceHandlerInterface
{


	/**
	 * retrieve record list
	 */
	public function find();


	/**
	 * create new record
	 */
	public function create();


	/**
	 * delete record
	 */
	public function delete($id);


	/**
	 * update record 
	 */
	public function update($id);

	/**
	 * load one record
	 */
	public function load($id);
	
}




