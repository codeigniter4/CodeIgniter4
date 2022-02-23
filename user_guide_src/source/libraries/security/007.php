<?php

public $globals = [
    'before' => [
        'csrf' => ['except' => ['api/record/save']],
    ],
];
