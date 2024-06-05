<?php

use Config\App;

$client = new \CodeIgniter\HTTP\CURLRequest(
    config(App::class),
    new \CodeIgniter\HTTP\URI(),
    new \CodeIgniter\HTTP\Response(config(App::class)),
    $options
);
