<?php
ini_set('error_reporting', E_ALL);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Make sure it recognizes that we're testing.
$_SERVER['CI_ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');

// Load our paths config file from the XML includePath
require 'Paths.php';

//--------------------------------------------------------------------
// Load our TestCase
//--------------------------------------------------------------------

require  __DIR__ . '/CIUnitTestCase.php';
