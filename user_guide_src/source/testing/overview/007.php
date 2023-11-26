<?php

$config = new Config\Logger();
$logger = new CodeIgniter\Log\Logger($config);

// check verbatim the log message
$logger->log('error', "That's no moon");
$this->assertLogged('error', "That's no moon");

// check that a portion of the message is found in the logs
$exception = new RuntimeException('Hello world.');
$logger->log('error', $exception->getTraceAsString());
$this->assertLogContains('error', '{main}');
