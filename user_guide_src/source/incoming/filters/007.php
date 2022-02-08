<?php

public $globals = [
    'before' => [
        'csrf' => ['except' => ['foo/*', 'bar/*']],
    ],
    'after' => [],
];
