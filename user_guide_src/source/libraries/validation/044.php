<?php

$validation = \Config\Services::validation();
$validation->setRules([
    'username' => 'required',
    'password' => 'required|min_length[10]',
]);

$data = [
    'username'   => 'john',
    'password'   => 'BPi-$Swu7U5lm$dX',
    'csrf_token' => '8b9218a55906f9dcc1dc263dce7f005a',
];

if ($validation->run($data)) {
    $validatedData = $validation->getValidated();
    // $validatedData = [
    //     'username' => 'john',
    //     'password' => 'BPi-$Swu7U5lm$dX',
    // ];
}
