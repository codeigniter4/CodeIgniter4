<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    // ...
    protected array $casts = [
        'id'       => 'int',
        'currency' => 'string',
        'amount'   => 'float[2,even]',
    ];
    // ...
}
