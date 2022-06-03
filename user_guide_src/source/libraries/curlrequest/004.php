<?php

$client = new \CodeIgniter\HTTP\CURLRequest(
    new \Config\App(),
    new \CodeIgniter\HTTP\URI(),
    new \CodeIgniter\HTTP\Response(new \Config\App()),
    $options
);
