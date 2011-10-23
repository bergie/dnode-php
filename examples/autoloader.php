<?php
// Use standard autoloader to load DNode and Evenement
require_once __DIR__.'/../vendor/symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('DNode', __DIR__.'/../src');
$loader->registerNamespace('Evenement', __DIR__.'/../vendor/Evenement/src');
$loader->register();
