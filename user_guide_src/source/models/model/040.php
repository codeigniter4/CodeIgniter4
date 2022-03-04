<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email,id,4]',
    ];
}
