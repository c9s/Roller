<?php
namespace Roller\Dumper;
use ReflectionFunction;
use SplFileObject;

class ClosureSerializer
{

    /**
     * serialize closure
     *
     * @param Closure 
     */
    static function serialize($closure)
    {
        $ref = new ReflectionFunction($closure);
        $file = new SplFileObject($ref->getFileName());
        $file->seek($ref->getStartLine()-1);
        $code = '';
        while ($file->key() < $ref->getEndLine())
        {
            $code .= $file->current();
            $file->next();
        }
        $start = strpos($code, 'function');
        $end = strrpos($code, '}') + 1;
        return substr($code, $start, $end - $start);
    }
}


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
            return ClosureSerializer::serialize($data);
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


