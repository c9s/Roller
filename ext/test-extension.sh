#!/bin/bash
SRCDIR=$(PWD)
EXTDIR=$(PWD)/ext
cd $EXTDIR 
which phpunit
make && (
    cd $SRCDIR
    DYLD_LIBRARY_PATH=$EXTDIR/modules php -d extension=roller.so $(which phpunit) tests

    # DYLD_LIBRARY_PATH=$EXTDIR/modules php -d extension_dir=$EXTDIR/modules -d extension=roller.so $(which phpunit) tests
    # phpunit -d extension_dir=$EXTDIR/modules -d extension=roller.so tests
)
