#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_roller.h"
#include "ext/pcre/php_pcre.h"
#include "ext/standard/php_string.h"

#define ZEND_HASH_FETCH(hash,key,ret) \
    zend_hash_find(hash, key, sizeof(key), (void**)&ret) == SUCCESS

// #define DEBUG 1

static const zend_function_entry roller_functions[] = {
    PHP_FE(roller_dispatch, NULL)
    PHP_FE(roller_build_route, NULL)
    PHP_FE_END
};

zend_module_entry roller_module_entry = {
	STANDARD_MODULE_HEADER,
    PHP_ROLLER_EXTNAME,
	roller_functions,
    NULL,
	NULL,
	NULL,
	NULL,
    NULL,
	PHP_ROLLER_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_ROLLER
ZEND_GET_MODULE(roller)
#endif


PHP_FUNCTION(roller_build_route)
{
    char * path;
    int    path_len;
    zval * callback;
    zval * options;

    // private function buildRoute($path,$callback,$options = array() )
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sz|a", 
                    &path,  &path_len,
                    &callback,
                    &options
                    ) == FAILURE) {
        RETURN_FALSE;
    }

    HashTable *options_hash;
    HashPosition options_position;

    options_hash = Z_ARRVAL_P(options);

    // allocate new zval
    zval * z_route;
    ALLOC_INIT_ZVAL(z_route);
    array_init(z_route);

    // copy path
    add_assoc_string( z_route , "path" , path, 1);

    char * p;
    if( Z_TYPE_P(callback) == IS_STRING 
            && (p = strchr(Z_STRVAL_P(callback),':')) != NULL )
    {
        char * callback_str;
        int    callback_len;

        char * className;
        char * methodName;
        zval * z_cb;

        callback_str = Z_STRVAL_P(callback);
        callback_len = Z_STRLEN_P(callback);

        className  = estrndup( callback_str, p - callback_str );
        methodName = estrndup( callback_str + ( p - callback_str ) + 1 , callback_len - ( p - callback_str ) );

        ALLOC_INIT_ZVAL( z_cb );
        array_init( z_cb );
        add_index_string( z_cb , 0 , className , 1 );
        add_index_string( z_cb , 1 , methodName , 1 );
        add_assoc_zval( z_route , "callback" , z_cb );
    }
    else {
        add_assoc_zval( z_route , "callback" , callback );
    }


    
    zval ** tmpval;
    if ( ZEND_HASH_FETCH(options_hash,"requirement",tmpval)  ) {
        add_assoc_zval( z_route , "requirement" , *tmpval );
    } else {
        // parse requirement from option hash
        /*
            foreach( $options as $k => $v ) {
                if( $k[0] !== ':' ) {
                    $requirement[ $k ] = $v;
                }
            }
        */
        zval * z_requirements;
        ALLOC_INIT_ZVAL( z_requirements );
        array_init( z_requirements );

        zval ** options_value;
        for(zend_hash_internal_pointer_reset_ex(options_hash, &options_position); 
            zend_hash_get_current_data_ex(options_hash, (void**) &options_value, &options_position) == SUCCESS; 
            zend_hash_move_forward_ex(options_hash, &options_position) )
        {
            // char *options_value = Z_STRVAL_PP(options_value);

            // fetch key
            char * key;
            unsigned int    key_len;
            unsigned long   index;
            if (zend_hash_get_current_key_ex(
                    options_hash, &key, &key_len, &index, 0, &options_position ) == HASH_KEY_IS_STRING ) 
            {

                /**
                 * If :key is supplied, it's a requirement. 
                 *
                 * We should check first char only.
                 * */
                char * pos;
                if( *key == ':' ) {
                    // if( (pos = strchr(key,':')) != NULL ) {
                    // add to requirement zval
                    zval *tmp;
                    ALLOC_INIT_ZVAL( tmp );
                    MAKE_COPY_ZVAL( options_value , tmp );
                    add_assoc_zval( z_requirements , key , tmp );
                }
            }
        }
        add_assoc_zval( z_route , "requirement" , z_requirements );
    }


    if ( ZEND_HASH_FETCH(options_hash,"secure",tmpval)  ) {
        add_assoc_bool( z_route , "secure" , 1 );
    }

    if ( ZEND_HASH_FETCH(options_hash,"default",tmpval)  ) {
        add_assoc_zval( z_route , "default" , *tmpval );
    }

    if ( ZEND_HASH_FETCH(options_hash,"method",tmpval)  ) {
        add_assoc_zval( z_route , "method" , *tmpval );
    }
    else if ( ZEND_HASH_FETCH(options_hash,"post",tmpval) ) {
        add_assoc_string( z_route, "method", "post", 0 );
    }
    else if ( ZEND_HASH_FETCH(options_hash,"get",tmpval) ) {
        add_assoc_string( z_route, "method", "get", 0 );
    }
    else if ( ZEND_HASH_FETCH(options_hash,"head",tmpval) ) {
        add_assoc_string( z_route, "method", "head", 0 );
    }
    else if ( ZEND_HASH_FETCH(options_hash,"delete",tmpval) ) {
        add_assoc_string( z_route, "method", "delete", 0 );
    }
    else if ( ZEND_HASH_FETCH(options_hash,"put",tmpval) ) {
        add_assoc_string( z_route, "method", "put", 0 );
    }

    if ( ZEND_HASH_FETCH(options_hash,"before",tmpval)  ) {
        add_assoc_bool( z_route , "before", 1 );
    }

    if ( ZEND_HASH_FETCH(options_hash,"args",tmpval)  ) {
        add_assoc_bool( z_route , "args", 1 );
    } else {
        add_assoc_null( z_route , "args" );
    }

    if ( ZEND_HASH_FETCH(options_hash,"name",tmpval)  ) {
        add_assoc_zval( z_route , "name", *tmpval );
    }
    else {
        zval * replacement;
        MAKE_STD_ZVAL(replacement);

        replacement->type = IS_STRING;
        replacement->value.str.len = strlen("_");
        replacement->value.str.val = estrdup("_");

        char * result;
        int result_len;
        int replace_count;

        if( *path == '/' ) {
            ++path;
            --path_len;
        }
        result = php_pcre_replace( 
                "/\\W+/" , sizeof("/\\W+/"),  
                path, path_len,
                replacement, 0 ,
                &result_len, -1 , &replace_count
        );
        add_assoc_string( z_route, "name" , result, 1);
    }

    *return_value = *z_route;
    zval_copy_ctor(return_value);
    return;
}

PHP_FUNCTION(roller_dispatch)
{
    // RETURN_STRING("Hello World", 1);
    zval *routeset;
    char *path;
    int  path_len;

    zval *z_subpats = NULL;	/* Array for subpatterns */

    /* parse parameters */
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "as|z", 
                    &routeset, 
                    &path, &path_len,
                    &z_subpats
                    ) == FAILURE) {
        RETURN_FALSE;
    }

    if( z_subpats == NULL ) {
        ALLOC_INIT_ZVAL( z_subpats );
    }



    /* get request method */
    char *c_request_method;
    int  c_request_method_len;
    zval **z_server_hash;
    zval **z_request_method;


	if (zend_hash_find(&EG(symbol_table), "_SERVER", sizeof("_SERVER"), (void **) &z_server_hash) == SUCCESS &&
		Z_TYPE_PP(z_server_hash) == IS_ARRAY &&
		zend_hash_find(Z_ARRVAL_PP(z_server_hash), "REQUEST_METHOD", sizeof("REQUEST_METHOD"), (void **) &z_request_method) == SUCCESS
	) {
		c_request_method = Z_STRVAL_PP(z_request_method);
        c_request_method_len = Z_STRLEN_PP(z_request_method);

        // convert to lower case, for comparing string
        php_strtolower(c_request_method ,c_request_method_len);
	}

    HashPosition route_pointer;
    HashTable    *routeset_hash;

    routeset_hash = Z_ARRVAL_P(routeset);

    zval **z_route;

    for(zend_hash_internal_pointer_reset_ex(routeset_hash, &route_pointer); 
            zend_hash_get_current_data_ex(routeset_hash, (void**) &z_route, &route_pointer) == SUCCESS; 
            zend_hash_move_forward_ex(routeset_hash, &route_pointer)) 
    {
        zval  **z_compiled;
        zval  **z_method;
        HashTable *route_hash = Z_ARRVAL_PP(z_route);


#ifdef DEBUG
        zval **z_path;
        if (zend_hash_find(route_hash, "path", sizeof("path"), (void**)&z_path) == SUCCESS ) {
            php_printf("D: check route: %s\n", Z_STRVAL_PP( z_path ) );
        }
#endif


        /* If 'compiled' key is not set, we should skip it */
        if (zend_hash_find(route_hash, "compiled", sizeof("compiled"), (void**)&z_compiled) == FAILURE ) {
            continue;
        }

        /* check request method */
        if (zend_hash_find(route_hash, "method", sizeof("method"), (void**) &z_method) != FAILURE ) {
            char *c_route_method = Z_STRVAL_PP(z_method);

            // If method is specified, we should check
            if( c_route_method != NULL 
                && strncmp(c_route_method, c_request_method, c_request_method_len ) != 0 
                ) continue;
        }

        if (Z_TYPE_PP(z_compiled) == IS_STRING) {

            /* parameters */
            char			 *regex;			/* Regular expression */
            int				  regex_len;
            pcre_cache_entry *pce;				/* Compiled regular expression */
            long			  flags = 0;		/* Match control flags */
            long			  start_offset = 0;	/* Where the new search starts */
            int               global  = 0;

            regex = estrndup(Z_STRVAL_PP(z_compiled), Z_STRLEN_PP(z_compiled));
            regex_len = strlen(regex);


            /* Compile regex or get it from cache. */
            if ((pce = pcre_get_compiled_regex_cache(regex, regex_len TSRMLS_CC)) == NULL) {
                RETURN_FALSE;
            }
            efree(regex);


            zval *pcre_ret;
            ALLOC_INIT_ZVAL(pcre_ret);
            php_pcre_match_impl(pce, path, path_len, pcre_ret, z_subpats,
                global, false , flags, start_offset TSRMLS_CC);


            /* return_value is not bool */
            if( Z_TYPE_P(pcre_ret) == IS_LONG && ! Z_LVAL_P(pcre_ret) )
                continue;


            /* apply variables
            foreach( $route['variables'] as $k ) {
                if( isset($regs[$k]) ) {
                    $route['vars'][ $k ] = $regs[$k];
                } else {
                    $route['vars'][ $k ] = $route['default'][ $k ];
                }
            }
            */

            /* check request method */
            zval **z_variables;
            zval **z_var_name;
            zval **z_default_array;

            HashTable *subpats_hash = NULL;

            if( z_subpats != NULL )
                subpats_hash = Z_ARRVAL_P(z_subpats);

#ifdef DEBUG
            php_printf("D: found route\n");
#endif



            // create a new route with variables

            zval *z_route_copy;
            ALLOC_INIT_ZVAL( z_route_copy );
            MAKE_COPY_ZVAL( z_route, z_route_copy );

            route_hash = Z_ARRVAL_P(z_route_copy);

            // Apply variables and default variables {{{
            // Check if variables key is defined.
            if (zend_hash_find(route_hash, "variables", sizeof("variables"), (void**) &z_variables) == SUCCESS ) {

#ifdef DEBUG
                php_printf("D: variables key found.\n");
#endif


                HashPosition  variables_pointer;
                HashTable    *variables_hash;

                variables_hash = Z_ARRVAL_PP(z_variables);

                // foreach variables as var, check if url contains variable or we should apply default value
                for(zend_hash_internal_pointer_reset_ex(variables_hash, &variables_pointer); 
                        zend_hash_get_current_data_ex(variables_hash, (void**) &z_var_name, &variables_pointer) == SUCCESS; 
                        zend_hash_move_forward_ex(variables_hash, &variables_pointer)) 
                {
                    // setup vars to route_hash table
                    zval **z_var_value;
                    zval **z_vars;
                    zval **z_default_value;
                    HashTable *vars_hash;

                    // register variable value to $regs['vars'][ $var_name ] = $var_value;
                    if( zend_hash_find(route_hash, "vars", sizeof("vars"), (void**) &z_vars ) == FAILURE ) {
                        zval *arr;
                        ALLOC_INIT_ZVAL(arr);
                        array_init(arr);
                        z_vars = &arr;
                        add_assoc_zval( z_route_copy, "vars" , *z_vars );
                    }

                    if( z_subpats != NULL
                        && zend_hash_find(subpats_hash, Z_STRVAL_PP(z_var_name), Z_STRLEN_PP(z_var_name) + 1,
                                    (void**) &z_var_value ) == SUCCESS 
                         )
                    {
#ifdef DEBUG
                        php_printf("D: assign variable %s.\n", Z_STRVAL_PP(z_var_name) );
#endif
                        add_assoc_zval( *z_vars , Z_STRVAL_PP(z_var_name) , *z_var_value );
                    } 
                    else if ( 
                        zend_hash_find(route_hash, "default" , sizeof("default"), (void**) &z_default_array ) != FAILURE 
                            && zend_hash_find( Z_ARRVAL_PP(z_default_array) , Z_STRVAL_PP(z_var_name), Z_STRLEN_PP(z_var_name) + 1, 
                                    (void**) &z_default_value ) != FAILURE ) 
                    {
#ifdef DEBUG
                        php_printf("D: assign default %s.\n", Z_STRVAL_PP(z_var_name) );
#endif
                        add_assoc_zval( *z_vars , Z_STRVAL_PP(z_var_name) , *z_default_value );
                    }
                }

            }
            // }}}

#ifdef DEBUG
            php_printf("D: return\n");
#endif
            *return_value = *z_route_copy;
            zval_copy_ctor(return_value);
            return;
        }
    }

#ifdef DEBUG
    php_printf("D: return null\n");
#endif
    RETURN_FALSE;
}

