<?php

$validation->setRules([
    'id'    => 'is_natural_no_zero',
    'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
]);
