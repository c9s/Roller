#!/bin/bash
make && \
    DYLD_LIBRARY_PATH=`pwd`/modules phpunit -d extension=roller.so tests
