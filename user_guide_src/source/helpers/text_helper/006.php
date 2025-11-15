<?php

$string = 'http://example.com//index.php';
echo reduce_double_slashes($string); // results in "http://example.com/index.php"
