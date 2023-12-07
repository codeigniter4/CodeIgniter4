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
use Tests\Support\Entity\UUID;

class UUIDPkeyModel extends Model
{
    protected $table            = 'uuid';
    protected $useAutoIncrement = false;
    protected $returnType       = UUID::class;
    protected $allowedFields    = [
        'value',
    ];
}
