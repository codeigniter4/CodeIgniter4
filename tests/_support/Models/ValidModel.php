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

class ValidModel extends Model
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
            'min_length[3]',
        ],
        'token' => 'permit_empty|in_list[{id}]',
    ];
    protected $validationMessages = [
        'name' => [
            'required'   => 'You forgot to name the baby.',
            'min_length' => 'Too short, man!',
        ],
    ];
}
