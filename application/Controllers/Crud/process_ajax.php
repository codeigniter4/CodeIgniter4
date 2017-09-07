<?php

error_reporting(E_ALL);
require_once 'harviacode.php';
require_once 'helper.php';
require_once 'process.php';

foreach ($hasil as $h) {
    echo '<p>' . $h . '</p>';
}
