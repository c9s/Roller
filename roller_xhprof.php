<?php
require 'tests/bootstrap.php';
require 'tests/RESTfulTest.php';
// start profiling
xhprof_enable();


// do something here
$router = new Roller\Router( null, array( 
    'cache_id' => 'roller_xhprof',
    'cache_dir' => 'cache',
));
$restful = new Roller\Plugin\RESTful(array( 'prefix' => '/restful' ));
$restful->registerResource( 'blog' , 'BlogResourceHandler' );
$router->addPlugin($restful);
$_SERVER['REQUEST_METHOD'] = 'GET';
$r = $router->dispatch('/restful/blog/1');
if($r) $r();

// stop profiler
$xhprof_data = xhprof_disable();

// Saving the XHProf run
// using the default implementation of iXHProfRuns.
include_once "xhprof_lib/utils/xhprof_lib.php";
include_once "xhprof_lib/utils/xhprof_runs.php";

$xhprof_runs = new XHProfRuns_Default();

// Save the run under a namespace "xhprof_foo".
//
// **NOTE**:
// By default save_run() will automatically generate a unique
// run id for you. [You can override that behavior by passing
// a run id (optional arg) to the save_run() method instead.]
$profiler_namespace = 'xhprof_roller';
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_roller");
$profiler_url = sprintf('http://xhprof.dev/index.php?run=%s&source=%s',$run_id, $profiler_namespace);
echo "<a href=$profiler_url>xhprof profiling</a>";
