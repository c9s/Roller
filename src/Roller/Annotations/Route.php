<?php
namespace Roller\Annotations;


/**
 * @Annotation
 */
class Route
{
    public $name;

    public $requirements;

    public $default;

    public $vars;

    public $value; // path

    public function toRoute()
    {
        return array(
            'name' => $this->name,
            'requirement' => $this->requirements,
            'default' => $this->default,
            'vars' => $this->vars,
            'path' => $this->value,
            'args' => array() ,
        );
    }

}
