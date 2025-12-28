<?php

use CodeIgniter\Session\Session;
use CodeIgniter\Session\Handlers\ArrayHandler;
use Config\Session;

// Load session config
$config = new config(Session::class);

// Initialize ArrayHandler with config and optional IP
$arrayHandler = new ArrayHandler($config, '127.0.0.1');

// Create session instance for testing
$testSession = new Session($arrayHandler, $config);

