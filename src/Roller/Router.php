<?php
namespace Roller;

class Router
{
	public $routes;


	/* boolean, is cache enabled ? */
	public $cache;


    /**
     * is cache found ?
     */
	public $hasCache = false;


    /**
     * cache directory
     *
     * @var string path
     */
	public $cacheDir;

    /**
     * cache id 
     *
     * @var string cache if for apc and file.
     */
	public $cacheId;


    /**
     * cache exxpiry 
     */
    public $cacheExpiry = 3600;

    /**
     * should we reload cache ? (not implemented yet)
     *
     */
	public $reload = false;


    /**
     * @var array plugins
     */
    public $plugins = array();


	const cache_type_apc = 1;

	const cache_type_file = 2;

	function __construct($routes = null, $options = array() )
	{

		/* if cache_id is defined (only), we use apc for caching defined routes */
		if( isset($options['cache_id']) ) {
			$this->cacheId = $options['cache_id'];
			$this->cache = self::cache_type_apc;
			if( false !== ($c = apc_fetch($options['cache_id'])) ) {
				$this->routes =  eval($c);
				$this->hasCache = true;
			}
		}

		if( isset($options['cache_dir']) ) {
			$this->cacheDir = $options['cache_dir'];
			$this->cache = self::cache_type_file;
			$cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $this->cacheId;
			$this->hasCache = false;
			if( file_exists($cacheFile) ) {
                // check expiry 
                if( filemtime($cacheFile) + $this->cacheExpiry > time() ) {
                    // expired, do something ?
                }
                else {
                    $this->routes = require $cacheFile;
                    $this->hasCache = true;
                }

			}
		}

		if( isset($options['reload']) ) {
			$this->reload = $options['reload'];
		}

		if( $this->routes === null ) {
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
			apc_store( $this->cacheId, $code ) === true 
				or die('Roller\Router: apc cache failed.');
		}
		elseif( $this->cache === self::cache_type_file ) {
			$dumper = new \Roller\Dumper\PhpDumper;
			$code = $dumper->dump( $this->routes );
			$cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $this->cacheId;
			file_put_contents( $cacheFile , '<?php ' . $code ) !== false
				or die('Roller\Router: file cache failed.');
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

                // if method is defined, we should check server request method
                if( isset($route['method']) && $m = $route['method'] ) {
                    /* 
                     * Which request method was used to access the page; 
                     * i.e. 'GET', 'HEAD', 'POST', 'PUT'.
                     */
                    if( strtolower( $_SERVER['REQUEST_METHOD'] ) !== $m ) {
                        continue;
                    }
                }

                // matched!
                return new MatchedRoute($route);
            }
        }
		return false;
	}

}
