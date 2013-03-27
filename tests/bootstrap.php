<?php
require 'PHPUnit/TestMore.php';
require 'Universal/ClassLoader/BasePathClassLoader.php';
$loader = new \Universal\ClassLoader\BasePathClassLoader( array('src','vendor/pear', 'tests'));
$loader->useIncludePath(true);
$loader->register();
