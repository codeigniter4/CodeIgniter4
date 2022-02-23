<?php

public $globals = [
    'before' => [
        'csrf' => ['except' => ['api/record/[0-9]+']],
    ],
];
