#!/bin/bash
SRCDIR=$(PWD)
EXTDIR=$(PWD)/ext
cd $EXTDIR && make && (
    cd $SRCDIR
    DYLD_LIBRARY_PATH=$EXTDIR/modules phpunit -d extension=roller.so tests
)
