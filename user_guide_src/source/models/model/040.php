<?php

protected $validationRules = [
    'email' => 'required|valid_email|is_unique[users.email,id,4]'
];
