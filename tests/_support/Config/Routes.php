<?php

/**
 * This is a simple file to include for testing the RouteCollection class.
 */

$routes->add('testing', 'TestController::index', ['as' => 'testing-index']);

/*Will throw error if filter is not found, thus testing the custom filter ability*/
$routes->add('testingfilter', 'TestController::index',  ['filter' => 'test-customfilter']);
