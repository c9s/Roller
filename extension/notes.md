


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

    EG(function_table)
    EG(symbol_table)
    EG(class_table)

    EG(regular_list)
    EG(persistent_list)

To get array hash table
    HashTable *route_hash = Z_ARRVAL_PP(z_route);


PHP API Functions:

    php_strtolower(c_request_method ,c_request_method_len);

    ALLOC_INIT_ZVAL(z_route_copy);

