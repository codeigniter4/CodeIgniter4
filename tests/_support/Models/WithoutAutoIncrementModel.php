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

class WithoutAutoIncrementModel extends Model
{
    protected $table         = 'without_auto_increment';
    protected $primaryKey    = 'key';
    protected $allowedFields = [
        'key',
        'value',
    ];
    protected $useAutoIncrement = false;
}
