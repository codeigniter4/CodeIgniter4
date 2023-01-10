<?php

use CodeIgniter\Publisher\Publisher;

$source    = service('autoloader')->getNamespace('CodeIgniter\\Shield')[0];
$publisher = new Publisher($source, APPPATH);

$file = APPPATH . 'Config/Auth.php';

$publisher->replace(
    $file,
    [
        'use CodeIgniter\Config\BaseConfig;' . "\n" => '',
        'class App extends BaseConfig'              => 'class App extends \Some\Package\SomeConfig',
    ]
);
