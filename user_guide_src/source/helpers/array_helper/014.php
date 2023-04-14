<?php

$result = array_group_by($employees, ['gender', 'hr.department']);

$result = [
    '' => [
        'Engineering' => [
            [
                'id'         => 1,
                'first_name' => 'Urbano',
                'gender'     => null,
                'hr'         => [
                    'country'    => 'Canada',
                    'department' => 'Engineering',
                ],
            ],
        ],
        'Sales' => [
            [
                'id'         => 4,
                'first_name' => 'Richy',
                'gender'     => null,
                'hr'         => [
                    'country'    => null,
                    'department' => 'Sales',
                ],
            ],
            [
                'id'         => 5,
                'first_name' => 'Mandy',
                'gender'     => null,
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Sales',
                ],
            ],
        ],
    ],
    'Male' => [
        'Marketing' => [
            [
                'id'         => 2,
                'first_name' => 'Case',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Marketing',
                ],
            ],
            [
                'id'         => 8,
                'first_name' => 'Tabby',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Marketing',
                ],
            ],
            [
                'id'         => 10,
                'first_name' => 'Somerset',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'Germany',
                    'department' => 'Marketing',
                ],
            ],
        ],
        'Engineering' => [
            [
                'id'         => 7,
                'first_name' => 'Alfred',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Engineering',
                ],
            ],
        ],
        'Sales' => [
            [
                'id'         => 9,
                'first_name' => 'Ario',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Sales',
                ],
            ],
        ],
    ],
    'Female' => [
        'Engineering' => [
            [
                'id'         => 3,
                'first_name' => 'Emera',
                'gender'     => 'Female',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Engineering',
                ],
            ],
            [
                'id'         => 6,
                'first_name' => 'Risa',
                'gender'     => 'Female',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Engineering',
                ],
            ],
        ],
    ],
];
