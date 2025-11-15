<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Widget extends Entity
{
    protected $casts = [
        'colors' => 'csv',
    ];
}
