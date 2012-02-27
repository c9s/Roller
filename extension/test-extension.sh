#!/bin/bash
DYLD_LIBRARY_PATH=`pwd`/modules php -d extension=xrouter.so test.php
