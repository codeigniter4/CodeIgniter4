<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Entity;

use CodeIgniter\Entity\Entity;
use Tests\Support\Entity\Cast\CastBinaryUUID;

class UUID extends Entity
{
    protected $casts = [
        'id' => 'uuid',
    ];
    protected $castHandlers = [
        'uuid' => CastBinaryUUID::class,
    ];
}
