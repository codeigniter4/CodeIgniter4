<?php

define('CONFIGPATH', __DIR__ . '/app/Config/');
define('PUBLICPATH', __DIR__ . '/app/public/');
define('HOMEPATH', __DIR__);

require 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once 'system/bootstrap.php';
