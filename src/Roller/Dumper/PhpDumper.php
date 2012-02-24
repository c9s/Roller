<?php
namespace Roller\Dumper;
use ReflectionFunction;
use SplFileObject;

/**
 * solution for var_export
 */
class PhpDumper 
{
    const space = '  ';

    function dumpVar($data,$level = 0)
    {
        if( is_array($data) ) 
        {
            $level++;
            $str = "array( \n";
            foreach( $data as $k => $v ) {
                if( is_integer($k) ) {
                    $str .= str_repeat( static::space ,$level) . $this->dumpVar($v,$level + 1) . ",\n";
                }
                else {
                    $str .= str_repeat( static::space ,$level) . "'$k' => " . $this->dumpVar($v, $level + 1) . ",\n";
                }
            }
            $str .= str_repeat( static::space ,$level > 0 ? $level - 1 : 0) . ")";
            return $str;
        }
        elseif( is_callable($data) && is_object($data) ) {
            return \Roller\ClosureSerializer::serialize($data);
        }
        return var_export($data,true);
    }

	function dump($routes)
	{
		$data = (array) $routes;
		$code = 'return Roller\RouteSet::__set_state(' . $this->dumpVar($data) . ');';
		return $code;
	}
}


