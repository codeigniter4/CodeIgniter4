<?php

$config = new LoggerConfig();
$logger = new Logger($config);

// ... do something that you expect a log entry from
$logger->log('error', "That's no moon");

$this->assertLogged('error', "That's no moon");
