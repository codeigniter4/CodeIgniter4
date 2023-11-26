<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    protected $validationRules = [
        'id'    => 'max_length[19]|is_natural_no_zero',
        'email' => 'required|max_length[254]|valid_email|is_unique[users.email,id,4]',
    ];
}
