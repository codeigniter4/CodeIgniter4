<?php

/**
 * -------------------------------------------------------------------
 * AUTO-LOADER
 * -------------------------------------------------------------------
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 */

/**
 * -------------------------------------------------------------------
 * Namespaces
 * -------------------------------------------------------------------
 * This maps the locations of any namespaces in your application
 * to their location on the file system. These are used by the
 * Autoloader to locate files the first time they have been instantiated.
 *
 * The '/application' and '/system' directories are already mapped for
 * you. You may change the name of the 'App' namespace if you wish,
 * but this should be done prior to creating any namespaced classes,
 * else you will need to modify all of those classes for this to work.
 *
 * DO NOT change the name of the CodeIgniter namespace or your application
 * WILL break. *
 * Prototype:
 *
 *   $config['psr4'] = [
 *       'CodeIgniter' => SYSPATH
 *   `];
 */
$config['psr4'] = [
	'App'         => realpath(APPPATH),
	'App\Controllers' => APPPATH.'Controllers',
	'CodeIgniter' => realpath(BASEPATH),
];

/**
 * -------------------------------------------------------------------
 * Class Map
 * -------------------------------------------------------------------
 * The class map provides a map of class names and their exact
 * location on the drive. Classes loaded in this manner will have
 * slightly faster performance because they will not have to be
 * searched for within one or more directories as they would if they
 * were being autoloaded through a namespace.
 *
 * Prototype:
 *
 *   $config['classmap'] = [
 *       'MyClass'   => '/path/to/class/file.php'
 *   ];
 */
$config['classmap'] = [
	'CodeIgniter\Benchmark\Timer'        => BASEPATH.'Benchmark/Timer.php',
	'CodeIgniter\Benchmark\Iterator'     => BASEPATH.'Benchmark/Iterator.php',
	'CodeIgniter\Router\RouteCollection' => BASEPATH.'Router/RouteCollection.php',
	'CodeIgniter\Router\Router'          => BASEPATH.'Router/Router.php',
];
