<?php

use CodeIgniter\Publisher\Publisher;

$source    = service('autoloader')->getNamespace('CodeIgniter\\Shield')[0];
$publisher = new Publisher($source, APPPATH);

$file = APPPATH . 'Config/App.php';

$publisher->addLineAfter(
    $file,
    '    public int $myOwnConfig = 1000;', // Adds this line
    'public bool $CSPEnabled = false;'     // After this line
);
