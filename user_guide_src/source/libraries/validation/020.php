<?php

$validation->setRules([
    'id'    => 'max_length[19]|is_natural_no_zero',
    'email' => 'required|max_length[254]|valid_email|is_unique[users.email,id,{id}]',
]);
