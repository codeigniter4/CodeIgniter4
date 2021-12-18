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

class EntityModel extends Model
{
    protected $table          = 'job';
    protected $returnType     = '\Tests\Support\Models\SimpleEntity';
    protected $useSoftDeletes = false;
    protected $dateFormat     = 'int';
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'name',
        'description',
        'created_at',
    ];
}
