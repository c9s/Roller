
PHP_ARG_ENABLE(roller,
    [Whether to enable the "roller" extension],
    [  --enable-roller      Enable "roller" extension support])

if test $PHP_ROLLER != "no"; then
    PHP_REQUIRE_CXX()
    PHP_SUBST(ROLLER_SHARED_LIBADD)
    PHP_ADD_LIBRARY(stdc++, 1, ROLLER_SHARED_LIBADD)
    PHP_NEW_EXTENSION(roller, php_roller.c, $ext_shared)
fi
