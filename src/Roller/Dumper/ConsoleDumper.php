<?php
namespace Roller\Dumper;
require 'ezc/Base/ezc_bootstrap.php';
spl_autoload_register( array( 'ezcBase', 'autoload' ) );
use ezcConsoleOutput;
use ezcConsoleTable;

class ConsoleDumper
{

	function dump($routes)
	{
		$out = new ezcConsoleOutput;

		/*
		$out->formats->headline->color = 'red';
		$out->formats->headline->style = array( 'bold' );

		$out->formats->sum->color = 'blue';
		$out->formats->sum->style = array( 'negative' );
		 */

		$table = new ezcConsoleTable( $out, 120 );
		$table[0][0]->content = 'Path';
		$table[0][1]->content = 'Pattern';
		$table[0][2]->content = 'Handler';
		$table[0][3]->content = 'Requirement';

		foreach( $routes->routes as $i => $route ) {
			// create new row
			$row = $table[ $i + 1];

			$path = $route['path'];
			// $pattern = str_replace(array("\n","\t"," "),'', $route['compiled'] );
			$pattern = $route['compiled'];
			$handler = \Roller\ClosureSerializer::serialize( $route['callback'] );
			$requirement = @$route['requirement'] ?: array();

			$row[0]->content = $path;
			$row[1]->content = $pattern;
			$row[2]->content = $handler;
			$row[3]->content = var_export($requirement,true);
		}
		$table->outputTable();
	}

}





