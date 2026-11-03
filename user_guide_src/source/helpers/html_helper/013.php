<?php

$attributes = [
    'class' => 'boldlist',
    'id'    => 'mylist',
];

$list = [
    'colors' => [
        'red',
        'blue',
        'green',
    ],
    'shapes' => [
        'round',
        'square',
        'circles' => [
            'ellipse',
            'oval',
            'sphere',
        ],
    ],
    'moods' => [
        'happy',
        'upset' => [
            'defeated' => [
                'dejected',
                'disheartened',
                'depressed',
            ],
            'annoyed',
            'cross',
            'angry',
        ],
    ],
];

echo ul($list, $attributes);
