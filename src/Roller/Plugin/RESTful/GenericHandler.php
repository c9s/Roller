<?php

namespace Roller\Plugin\RESTful;
use Exception;

abstract class GenericHandler extends BaseHandler
{

    abstract public function create($resource);
    abstract public function find($resource);
    abstract public function load($resource,$id);
    abstract public function update($resource,$id);
    abstract public function delete($resource,$id);

	public function handleFind($resource,$format)
	{
		$this->data = $this->find($resource);
		return $this->returnSuccess($format, "Record find success.");
	}

	public function handleCreate($resource,$format) 
	{
		$this->data = $this->create($resource);
		return $this->returnSuccess($format, "Record $id created.");
	}

	public function handleUpdate($resource,$id,$format)
	{
		$this->data = $this->update($resource,$id);
		return $this->returnSuccess($format, "Record $id updated.");
	}

	public function handleLoad($resource,$id,$format)
	{
		$this->data = $this->load($resource,$id);
		return $this->returnSuccess($format, "Record $id loaded.");
	}

	public function handleDelete($resource,$id,$format)
	{
		$this->data = $this->load($resource,$id);
		return $this->returnSuccess($format, "Record $id deleted.");
	}

    public function expand($routes, $h)
    {
        $routes->add( '/:resource(\.:format)' , array( $h , 'handleCreate' ), array( 
            ':method' => 'post',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource(\.:format)' , array( $h , 'handleFind' ), array( 
            ':method' => 'get',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource/:id(\.:format)' , array( $h, 'handleUpdate'), array( 
            ':method' => 'put',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource/:id(\.:format)' , array( $h, 'handleDelete'), array( 
            ':method' => 'delete',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource/:id(\.:format)' , array( $h, 'handleLoad'), array( 
            ':method' => 'get',
            ':default' => array( 'format' => 'json' ),
        ));
    }

}

