<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $casts = [
        'status' => 'enum[App\Enums\UserStatus]',
    ];
}
