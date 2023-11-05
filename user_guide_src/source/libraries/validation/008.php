<?php

$validation = \Config\Services::validation();
$request    = \Config\Services::request();

if ($validation->withRequest($request)->run()) {
    // If you use the input data, you should get it from the getValidated() method.
    // Otherwise you may create a vulnerability.
    $validData = $validation->getValidated();

    // ...
}
