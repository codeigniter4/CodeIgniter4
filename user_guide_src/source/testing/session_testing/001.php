<?php

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Session\Session;
use Config\Session as SessionConfig;

// Load session config
$config = config(SessionConfig::class);

// Initialize ArrayHandler with config and optional IP
$arrayHandler = new ArrayHandler($config, '127.0.0.1');

// Create session instance for testing
$testSession = new Session($arrayHandler, $config);
