<?php

namespace App\Models;

use App\Models\Cast\CastBase64;
use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    // Specify the type for the field
    protected array $casts = [
        'column1' => 'base64',
    ];

    // Bind the type to the handler
    protected array $castHandlers = [
        'base64' => CastBase64::class,
    ];

    // ...
}
