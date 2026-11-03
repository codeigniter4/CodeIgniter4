<?php

namespace App\Models;

use App\Models\Cast\SomeHandler;
use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    // Define a type with parameters
    protected array $casts = [
        'column1' => 'class[App\SomeClass, param2, param3]',
    ];

    // Bind the type to the handler
    protected array $castHandlers = [
        'class' => SomeHandler::class,
    ];

    // ...
}
