<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        'id'         => null,
        'name'       => null, // Represents a username
        'email'      => null,
        'password'   => null,
        'created_at' => null,
        'updated_at' => null,
    ];
}
