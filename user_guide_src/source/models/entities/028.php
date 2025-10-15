<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $datamap = [
        '_role' => '_role',
    ];

    protected $attributes = [
        '__secure' => 'On',
        '_role'    => 'user',
        'about'    => '',
    ];
}

$user = new User(['__secure' => 'Off', 'about' => 'Hi, I am John!', '_role' => 'admin']);

echo 'Secure: ' . $user->__secure;
print_r($user->toArray());
print_r($user->toRawArray());

/**
 * Output:
 *
 * Secure: Off
 * Array
 * (
 *     [about] => Hi, I am John!
 *     [_role] => admin
 * )
 * Array
 * (
 *     [__secure] => Off
 *     [_role] => admin
 *     [about] => Hi, I am John!
 * )
 */
