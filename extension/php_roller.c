#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_roller.h"
#include "ext/pcre/php_pcre.h"
#include "ext/standard/php_string.h"

// #define DEBUG 1

static const zend_function_entry roller_functions[] = {
    PHP_FE(roller_dispatch, NULL)
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
        RETURN_NULL();
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
    RETURN_NULL();
}

