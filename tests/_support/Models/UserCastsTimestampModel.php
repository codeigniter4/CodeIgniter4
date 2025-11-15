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
use Tests\Support\Entity\Cast\CastBase64;

class UserCastsTimestampModel extends Model
{
    protected $table         = 'user';
    protected $allowedFields = [
        'name',
        'email',
        'country',
    ];
    protected array $casts = [
        'id'         => 'int',
        'name'       => 'base64',
        'email'      => 'json-array',
        'created_at' => '?datetime',
        'updated_at' => '?datetime',
    ];
    protected array $castHandlers = [
        'base64' => CastBase64::class,
    ];
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';

    protected function initialize(): void
    {
        parent::initialize();

        if ($this->db->DBDriver === 'SQLSRV') {
            // SQL Server returns a string like `2023-11-27 01:44:04.000`.
            $this->casts['created_at'] = 'datetime[ms]';
            $this->casts['updated_at'] = 'datetime[ms]';
        }
    }
}
