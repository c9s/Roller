#!/bin/bash
make && \
    DYLD_LIBRARY_PATH=`pwd`/modules php -d extension=roller.so test.php
