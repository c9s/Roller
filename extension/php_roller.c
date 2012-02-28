#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_roller.h"
#include "ext/pcre/php_pcre.h"

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

    zval *subpats = NULL;	/* Array for subpatterns */

    /* parse parameters */
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "asz", 
                    &routeset, 
                    &path, &path_len,
                    &subpats
                    ) == FAILURE) {
        RETURN_NULL();
    }


    /* get request method */
    char *c_request_method;
    int  *c_request_method_len;
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
        // php_printf("%s\n", c_request_method );
	}

    HashPosition route_pointer;
    HashTable    *routeset_hash;

    routeset_hash = Z_ARRVAL_P(routeset);

    /*
    int array_count;
    array_count = zend_hash_num_elements(routeset_hash);
    php_printf("%d routes\n", array_count);
    */

    zval **z_route;
    for(zend_hash_internal_pointer_reset_ex(routeset_hash, &route_pointer); 
            zend_hash_get_current_data_ex(routeset_hash, (void**) &z_route, &route_pointer) == SUCCESS; 
            zend_hash_move_forward_ex(routeset_hash, &route_pointer)) 
    {
        zval  **z_compiled;
        zval  **z_method;
        HashTable *route_hash = Z_ARRVAL_PP(z_route);

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
            // PHPWRITE(Z_STRVAL_PP(z_compiled), Z_STRLEN_PP(z_compiled));

            /* parameters */
            char			 *regex;			/* Regular expression */
            int				  regex_len;
            pcre_cache_entry *pce;				/* Compiled regular expression */
            long			  flags = 0;		/* Match control flags */
            long			  start_offset = 0;	/* Where the new search starts */
            int  global  = 0;

            regex = estrndup(Z_STRVAL_PP(z_compiled), Z_STRLEN_PP(z_compiled));
            regex_len = strlen(regex);


            /* Compile regex or get it from cache. */
            if ((pce = pcre_get_compiled_regex_cache(regex, regex_len TSRMLS_CC)) == NULL) {
                RETURN_FALSE;
            }

            php_pcre_match_impl(pce, path, path_len, return_value, subpats,
                global, false , flags, start_offset TSRMLS_CC);

            efree(regex);

            /* return_value is not bool */
            if( Z_TYPE_P(return_value) == IS_LONG && ! Z_LVAL_P(return_value) )
                continue;

            MAKE_COPY_ZVAL( z_route, return_value );
            return;
        }
    }
    RETURN_NULL();
}

