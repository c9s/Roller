<?php
namespace Roller;
use Exception;

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


    public $matchedRouteClass = 'Roller\MatchedRoute';


    /**
     * cache id 
     *
     * @var string cache if for apc and file.
     */
	public $cacheId;


    public $enableExtension = true;

    /**
     * cache exxpiry 
     */
    public $cacheExpiry;

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

	public function __construct($routes = null, $options = array() )
	{
        /* setup custom route class */
        if( isset($options['route_class']) ) {
            $this->matchedRouteClass = $options['route_class'];
        }

		/* if cache_id is defined (only), we use apc for caching defined routes */
		if( isset($options['cache_id']) ) {
			$this->cacheId = $options['cache_id'];

            if( extension_loaded('apc') ) {
                $this->cache = self::cache_type_apc;
                if( false !== ($c = apc_fetch($options['cache_id'])) ) {
                    $this->routes =  eval($c);
                    $this->hasCache = true;
                }
            }
		}

		if( isset($options['cache_dir']) ) {
			$this->cacheDir = $options['cache_dir'];
			$this->cache = self::cache_type_file;
			$cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $this->cacheId;
			$this->hasCache = false;
			if( file_exists($cacheFile) ) {
                // check expiry 
                if( $this->cacheExpiry 
                    && ( filemtime($cacheFile) + $this->cacheExpiry ) < time() ) 
                {
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

	public function add($path,$callback,$options=array() )
	{
		return $this->routes->add( $path, $callback, $options );
	}

    public function mount($prefix,$routeset) 
    {
        return $this->routes->mount( $prefix , $routeset );
    }


    public function addPlugin($plugin)
    {
        $this->plugins[] = $plugin;
    }

	private function makeCache()
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

	public function dispatch($path)
	{
		if( ! $this->hasCache ) {

            foreach( $this->plugins as $p )
                $p->beforeCompile( $this );

			$this->routes->compile();

            foreach( $this->plugins as $p )
                $p->afterCompile( $this );

			if( $this->cache ) {
				// make cache
				$this->makeCache();
			}

			// we are already in runtime, doesn't need to reload cache or 
			// re-compile pattern.
			$this->hasCache = true;
		}

        if( $this->enableExtension && function_exists('roller_dispatch') ) {
            $route = roller_dispatch( $this->routes->routes , $path );
            $class = $this->matchedRouteClass;
            if( $route ) {
                return new $class($this,$route);
            }
            else {
                return false;
            }
        }
        else {
            $server_req_method = isset($_SERVER['REQUEST_METHOD']) ? strtolower( $_SERVER['REQUEST_METHOD'] ) : null;
            foreach( $this->routes as $route ) {
                if( preg_match( $route['compiled'], $path, $regs ) ) {

                    // if method is defined, we should check server request method
                    if( $server_req_method && isset($route['method']) && $m = $route['method'] ) {
                        /* 
                        * Which request method was used to access the page; 
                        * i.e. 'GET', 'HEAD', 'POST', 'PUT'.
                        */
                        if( $server_req_method !== $m )
                            continue;
                    }

                    // apply variables
                    foreach( $route['variables'] as $k ) {
                        if( isset($regs[$k]) ) {
                            $route['vars'][ $k ] = $regs[$k];
                        } 
                        elseif ( isset($route['default'][$k]) ) {
                            $route['vars'][ $k ] = $route['default'][ $k ];
                        }
                    }

                    // matched!
                    $class = $this->matchedRouteClass;
                    return new $class($this,$route);
                }
            }
        }
		return false;
	}


    /**
     * dispatch methods to plugins (mixin) and routeset methods
     */
    public function __call($m,$a)
    {
        if( method_exists($this->routes,$m) ) {
            return call_user_func_array( array($this->routes,$m) , $a );
        }
        foreach( $this->plugins as $p ) {
            if( method_exists($p,$m) ) {
                return call_user_func_array( array($p,$m), $a);
            }
        }
        throw new Exception("$m method not found.");
    }

}
