<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Models;

use CodeIgniter\Model;

class ValidErrorsModel extends Model
{
    protected $table          = 'job';
    protected $returnType     = 'object';
    protected $useSoftDeletes = false;
    protected $dateFormat     = 'int';
    protected $allowedFields  = [
        'name',
        'description',
    ];
    protected $validationRules = [
        'id'   => 'permit_empty|is_natural_no_zero',
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
