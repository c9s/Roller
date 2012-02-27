#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_roller.h"

static function_entry roller_functions[] = {
    PHP_FE(roller_test, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry roller_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_ROLLER_EXTNAME,
    roller_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
#if ZEND_MODULE_API_NO >= 20010901
    PHP_ROLLER_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_ROLLER
ZEND_GET_MODULE(roller)
#endif

PHP_FUNCTION(roller_test)
{
    RETURN_STRING("Hello World", 1);
}
