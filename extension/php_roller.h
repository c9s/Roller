

#ifndef PHP_ROLLER_H
#define PHP_ROLLER_H 1
#define PHP_ROLLER_VERSION "1.0"
#define PHP_ROLLER_EXTNAME "roller"

PHP_FUNCTION(roller_test);

extern zend_module_entry roller_module_entry;
#define phpext_roller_ptr &roller_module_entry

#endif
