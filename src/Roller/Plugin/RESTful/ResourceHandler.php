<?php
namespace Roller\Plugin\RESTful;
use Exception;

abstract class ResourceHandler extends BaseHandler
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
	 * expand resource handlers to routeset,
	 * in here, we define how to expand RESTful URLs from resource id, 
	 * and which is customizable.
	 *
	 * @param RouteSet $routes
	 * @param string $r resource identifier.
	 */
	public function expand($routes, $h, $r)
	{
        $class = is_object($h) ? get_class($h) : $h;
		$routes->add( "/$r(\.:format)" , array($class,'handleFind'), 
			array( 
				':get' => true , 
				':default' => array( 'format' => 'json' ) 
			));

		$routes->add( '/' . $r . '(\.:format)' , array($class,'handleCreate'), 
			array( 
				':post' => true, 
				':default' => array( 'format' => 'json' ) 
			));

		$routes->add( '/' . $r . '/:id(\.:format)' , array($class,'handleLoad'),
			array( 
				':get' => true, 
				':default' => array( 'format' => 'json' )
			));

		$routes->add( '/' . $r . '/:id(\.:format)' , array($class,'handleUpdate'),
			array( 
				':put' => true, 
				':default' => array( 'format' => 'json' ) 
			));

		$routes->add( '/' . $r . '/:id(\.:format)' , array($class,'handleDelete'),
			array( 
				':delete' => true, 
				':default' => array( 'format' => 'json' ) 
			));
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


}


