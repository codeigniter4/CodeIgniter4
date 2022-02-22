<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MyEntity extends Entity
{
    // Specifying the type for the field
    protected $casts = [
        'key' => 'base64',
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'base64' => \App\Entities\Cast\CastBase64::class,
    ];
}

// ...

$entity->key = 'test'; // dGVzdA==
echo $entity->key;     // test
