<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $casts = [
        'secret_note' => 'encrypted',
    ];
}

$user = new User();

$user->secret_note = 'Internal billing note';

echo $user->secret_note; // Internal billing note

$raw = $user->toRawArray();

echo $raw['secret_note']; // Base64-encoded encrypted value
