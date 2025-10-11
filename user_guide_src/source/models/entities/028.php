<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        '__secure' => 'On',
        'about'    => '',
    ];
}

$user = new User(['__secure' => 'Off', 'about' => 'Hi, I am John!']);

print_r($user->toArray());
print_r($user->toRawArray());

/**
 * Output:
 * (
 *     [about] => Hi, I am John!
 * )
 * Array
 * (
 *     [__secure] => Off
 *     [about]   => Hi, I am John!
 * )
 */
