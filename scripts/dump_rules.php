<?php
require 'tests/bootstrap.php';

$routes = new Roller\RouteSet;
$routes->add( '/blog/:year/:month' , function($year,$month) { return 'Yes'; },array( 'year' => '\d+' ));
$routes->add( '/blog/:id' , function($id) { return $id; },array( 'id' => '\d+' ));
$routes->compile();

$dumper = new Roller\Dumper\ConsoleDumper;
$dumper->dump( $routes );
