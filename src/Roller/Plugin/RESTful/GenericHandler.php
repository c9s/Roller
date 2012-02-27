<?php

namespace Roller\Plugin\RESTful;
use Exception;

abstract class GenericHandler extends BaseHandler
{

    abstract public function create($resourceId);
    abstract public function find($resourceId);
    abstract public function load($resourceId,$id);
    abstract public function update($resourceId,$id);
    abstract public function delete($resourceId,$id);


	public function handleFind($resourceId,$format)
	{
		$this->data = $this->find($resourceId);
		return $this->returnSuccess($format, "Record find success.");
	}

	public function handleCreate($resourceId,$format) 
	{
		$this->data = $this->create($resourceId);
		return $this->returnSuccess($format, "Record $id created.");
	}

	public function handleUpdate($resourceId,$id,$format)
	{
		$this->data = $this->update($resourceId,$id);
		return $this->returnSuccess($format, "Record $id updated.");
	}

	public function handleLoad($resourceId,$id,$format)
	{
		$this->data = $this->load($resourceId,$id);
		return $this->returnSuccess($format, "Record $id loaded.");
	}

	public function handleDelete($resourceId,$id,$format)
	{
		$this->data = $this->load($resourceId,$id);
		return $this->returnSuccess($format, "Record $id deleted.");
	}


    public function expand($routes, $h)
    {

        $routes->add( '/:resourceId(\.:format)' , array( $h , 'handleCreate' ), array( 
            ':method' => 'post',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resourceId(\.:format)' , array( $h , 'handleFind' ), array( 
            ':method' => 'get',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resourceId/:id(\.:format)' , array( $h, 'handleUpdate'), array( 
            ':method' => 'put',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resourceId/:id(\.:format)' , array( $h, 'handleDelete'), array( 
            ':method' => 'delete',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resourceId/:id(\.:format)' , array( $h, 'handleLoad'), array( 
            ':method' => 'get',
            ':default' => array( 'format' => 'json' ),
        ));

    }

}

