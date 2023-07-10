<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $validationRules = [
        'username'     => 'required|max_length[30]|alpha_numeric_space|min_length[3]',
        'email'        => 'required|max_length[254]|valid_email|is_unique[users.email]',
        'password'     => 'required|max_length[255]|min_length[8]',
        'pass_confirm' => 'required_with[password]|max_length[255]|matches[password]',
    ];
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Sorry. That email has already been taken. Please choose another.',
        ],
    ];
}
