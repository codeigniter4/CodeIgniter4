<?php
ini_set('error_reporting', E_ALL);
;
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Make sure it recognizes that we're testing.
$_SERVER['CI_ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');

// path to the directory that holds the front controller (index.php)
define('FCPATH', realpath(__DIR__ . '/../../') . '/public' . DIRECTORY_SEPARATOR);

// The path to the "tests" directory
define('TESTPATH', realpath(__DIR__ . '/../') . '/');

define('SUPPORTPATH', realpath(TESTPATH . '_support/') . '/');

// Set environment values that would otherwise stop the framework from functioning during tests.
if (! isset($_SERVER['app.baseURL']))
{
	$_SERVER['app.baseURL'] = 'http://example.com';
}

//--------------------------------------------------------------------
// Load our TestCase
//--------------------------------------------------------------------

require  __DIR__ . '/CIUnitTestCase.php';
