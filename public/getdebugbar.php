<?php

if (isset($_REQUEST['f'])) {
    $file = $_REQUEST['f'];
    $file = './' . str_replace('/', '', $file); // poor's man locking 
    echo file_get_contents($file);
    unlink($file);
}