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

namespace Tests\Support\Entity;

use CodeIgniter\Entity\Entity;

class Member extends Entity
{
    protected $attributes = [
        'id'         => 0,
        'name'       => 'unknown',
        'email'      => 'unknown',
        'country'    => 'unknown',
        'created_at' => '2026-11-10 14:50:00',
        'updated_at' => '2026-11-10 14:50:00',
        'deleted_at' => '2026-11-10 14:50:00',

        // The property is not stored in the database
        'profile' => 'unknown',
    ];

    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->attributes['profile'] = 'profile';
    }
}
