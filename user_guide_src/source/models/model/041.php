<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    protected $allowedFields = ['name', 'email', 'address'];
}
