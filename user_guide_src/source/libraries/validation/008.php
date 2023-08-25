<?php

$validation = \Config\Services::validation();
$request    = \Config\Services::request();

if ($validation->withRequest($request)->run()) {
    // If you want to get the validated data.
    $validData = $validation->getValidated();

    // ...
}
