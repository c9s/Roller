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

    zval			 *subpats = NULL;	/* Array for subpatterns */

    zval **data;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "asz", 
                    &routeset, 
                    &path, &path_len,
                    &subpats
                    ) == FAILURE) 
    {
        RETURN_NULL();
    }

    HashPosition pointer;
    int array_count;

    HashTable *arr_hash = Z_ARRVAL_P(routeset);
    array_count = zend_hash_num_elements(arr_hash);

    // php_printf("%d routes\n", array_count);

    for(zend_hash_internal_pointer_reset_ex(arr_hash, &pointer); 
            zend_hash_get_current_data_ex(arr_hash, (void**) &data, &pointer) == SUCCESS; 
            zend_hash_move_forward_ex(arr_hash, &pointer)) 
    {

        zval  **tmp;
        HashTable *route_hash = Z_ARRVAL_PP(data);
        if (zend_hash_find(route_hash, "compiled", sizeof("compiled"), (void**)&tmp) == SUCCESS) {
            if (Z_TYPE_PP(tmp) == IS_STRING) {
                // PHPWRITE(Z_STRVAL_PP(tmp), Z_STRLEN_PP(tmp));

                /* parameters */
                char			 *regex;			/* Regular expression */
                char			 *subject;			/* String to match against */
                int				  regex_len;
                int				  subject_len;
                pcre_cache_entry *pce;				/* Compiled regular expression */
                long			  flags = 0;		/* Match control flags */
                long			  start_offset = 0;	/* Where the new search starts */
                int  global  = 0;

                regex = estrndup(Z_STRVAL_PP(tmp), Z_STRLEN_PP(tmp));
                regex_len = strlen(regex);

                subject = path;
                subject_len = path_len;

                /* Compile regex or get it from cache. */
                if ((pce = pcre_get_compiled_regex_cache(regex, regex_len TSRMLS_CC)) == NULL) {
                    RETURN_FALSE;
                }

                php_pcre_match_impl(pce, subject, subject_len, return_value, subpats,
                    global, false , flags, start_offset TSRMLS_CC);

                /* return_value is not bool */
                if( Z_TYPE_P(return_value) == IS_LONG ) {
                    if( Z_LVAL_P(return_value) ) {
                        return;
                    }
                }
            }
        }
    }
    RETURN_NULL();
}

