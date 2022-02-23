<?php

public $handlers = [
    // File Handler
    'CodeIgniter\Log\Handlers\FileHandler' => [
        'handles' => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
    ]
];
