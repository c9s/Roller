<?php
namespace Roller;

interface PluginInterface 
{
    public function beforeCompile($router);
    public function afterCompile($router);
}



