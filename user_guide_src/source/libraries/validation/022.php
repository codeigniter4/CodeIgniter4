<?php

$validation->setRules([
    'email' => 'required|valid_email|is_unique[users.email,id,4]',
]);
