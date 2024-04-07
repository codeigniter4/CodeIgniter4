<?php

declare(strict_types=1);

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
use Tests\Support\Entity\User;

class UserObjModel extends Model
{
    protected $table         = 'user';
    protected $allowedFields = [
        'name',
        'email',
        'country',
        'deleted_at',
    ];
    protected $returnType     = User::class;
    protected $useSoftDeletes = true;
    protected $dateFormat     = 'datetime';
}
