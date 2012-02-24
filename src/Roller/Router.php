<?php
namespace Roller;

class Router
{
	public $routes;


	/* boolean, is cache enabled ? */
	public $cache;

	public $hasCache;

	public $cacheDir;

	public $cacheId;

	public $reload = false;


	const cache_type_apc = 1;

	const cache_type_file = 2;

	function __construct($routes = null, $options = array() )
	{

		/* if cache_id is defined (only), we use apc for caching defined routes */
		if( isset($options['cache_id']) ) {
			$this->cacheId = $options['cache_id'];
			$this->cache = self::cache_type_apc;
			if( $c = apc_fetch($options['cache_id']) ) {
				$this->routes = eval($c);
				$this->hasCache = true;
			}
		}

		if( isset($options['cache_dir']) ) {
			$this->cacheDir = $options['cache_dir'];
			$this->cache = self::cache_type_file;
			$cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $this->cacheId;
			if( file_exists($cacheFile) ) {
				$this->routes = require $cacheFile;
				$this->hasCacheCache = true;
			}
		}

		if( isset($options['reload']) ) {
			$this->reload = $options['reload'];
		}

		if( !$this->routes ) {
			$this->routes = $routes ?: new RouteSet;
		}
	}

	function add($path,$callback,$options=array() )
	{
		return $this->routes->add( $path, $callback, $options );
	}

	function makeCache()
	{
		if( $this->cache === self::cache_type_apc ) {
			$dumper = new \Roller\Dumper\PhpDumper;
			$code = $dumper->dump( $this->routes );
			apc_store( $this->cacheId, $code ) === false 
				or die('Roller\Router: apc cache failed.');
		}
		elseif( $this->cache === self::cache_type_file ) {
			$dumper = new \Roller\Dumper\PhpDumper;
			$code = $dumper->dump( $this->routes );
			$cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $this->cacheId;
			file_put_contents( $cacheFile , '<?php ' . $code );
		}
		else {
			throw new Exception('Unknown cache type');
		}
	}

	function dispatch($path)
	{
		if( ! $this->hasCache ) {
			$this->routes->compile();
			if( $this->cache ) {
				// make cache
				$this->makeCache();
			}

			// we are already in runtime, doesn't need to reload cache or 
			// re-compile pattern.
			$this->hasCache = true;
		}

        foreach( $this->routes as $route ) {
            if( preg_match( $route['compiled'], $path, $regs ) ) {
                foreach( $route['variables'] as $k ) {
					if( isset($regs[$k]) ) {
						$route['vars'][ $k ] = $regs[$k];
					} else {
						$route['vars'][ $k ] = $route['default'][ $k ];
					}
                }
                return new MatchedRoute($route);
            }
        }
		return false;
	}

}
