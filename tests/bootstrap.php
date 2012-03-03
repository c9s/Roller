<?php
require 'vendor/pear/PHPUnit/TestMore.php';
require 'vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$loader = new \Universal\ClassLoader\BasePathClassLoader( array('src','vendor/pear', 'tests'));
$loader->useIncludePath(true);
$loader->register();
