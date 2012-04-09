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
		return $this->renderFormat($this->data ,$format );
	}

	public function handleCreate($resource,$format) 
	{
		$this->data = $this->create($resource);
		return $this->renderFormat($this->data ,$format );
	}

	public function handleUpdate($resource,$id,$format)
	{
		$this->data = $this->update($resource,$id);
		return $this->renderFormat($this->data ,$format );
	}

	public function handleLoad($resource,$id,$format)
	{
		$this->data = $this->load($resource,$id);
		return $this->renderFormat($this->data ,$format );
	}

	public function handleDelete($resource,$id,$format)
	{
		$this->data = $this->load($resource,$id);
		return $this->renderFormat($this->data ,$format );
	}

    public function expand($routes, $h)
    {
        $class = is_object($h) ? get_class($h) : $h;

        $routes->add( '/:resource(.:format)' , array( $class , 'handleCreate' ), array( 
            ':method' => 'post',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource(.:format)' , array( $class , 'handleFind' ), array( 
            ':method' => 'get',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource/:id(.:format)' , array( $class, 'handleUpdate'), array( 
            ':method' => 'put',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource/:id(.:format)' , array( $class, 'handleDelete'), array( 
            ':method' => 'delete',
            ':default' => array( 'format' => 'json' ),
        ));

        $routes->add( '/:resource/:id(.:format)' , array( $class, 'handleLoad'), array( 
            ':method' => 'get',
            ':default' => array( 'format' => 'json' ),
        ));
    }

}

