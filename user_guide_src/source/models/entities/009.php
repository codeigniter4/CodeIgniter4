<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        'id'         => null,
        'full_name'  => null, // In the $attributes, the key is the column name
        'email'      => null,
        'password'   => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $datamap = [
        'name' => 'full_name',
    ];
}
