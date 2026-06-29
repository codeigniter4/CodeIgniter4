<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $allowedFields = ['public_id', 'name', 'email'];

    protected $insertOnlyFields = ['public_id'];
}
