<?php

$fieldValidationMessage = [
    'name' => [
        'required'   => 'Your baby name is missing.',
        'min_length' => 'Too short, man!',
    ],
];
$model->setValidationMessages($fieldValidationMessage);
