<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // ...
    protected $casts = [
        'id'        => 'int',
        'birthdate' => '?datetime',
        'hobbies'   => 'json-array',
        'active'    => 'int-bool',
    ];
    // ...
}
