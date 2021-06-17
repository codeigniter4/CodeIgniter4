<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;

class ValidErrorsModel extends Model
{
    protected $table = 'job';

    protected $returnType = 'object';

    protected $useSoftDeletes = false;

    protected $dateFormat = 'int';

    protected $allowedFields = [
        'name',
        'description',
    ];

    protected $validationRules = [
        'name' => [
            'required',
            'min_length[10]',
            'errors' => [
                'min_length' => 'Minimum Length Error',
            ],
        ],
        'token' => 'in_list[{id}]',
    ];
}
