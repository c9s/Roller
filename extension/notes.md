


PHP5.4

    function_entry is changed to zend_function_entry

    ./configure --with-php-config=/opt/local/bin/php-config

    static zend_function_entry roller_functions[] = {
        PHP_FE(roller_dispatch, NULL)
        {NULL, NULL, NULL}
    };

    static const function_entry roller_functions[] = {
        PHP_FE(roller_dispatch, NULL)
        PHP_FE_END
    };



https://wiki.php.net/internals/engine/objects


    MAKE_COPY_ZVAL(subject, return_value);
