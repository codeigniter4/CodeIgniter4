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

use CodeIgniter\Entity;

class User extends Entity
{
    /**
     * Maps names used in sets and gets against unique
     * names within the class, allowing independence from
     * database column names.
     *
     * Example:
     *  $datamap = [
     *      'db_name' => 'class_name'
     *  ];
     */
    protected $datamap = [];

    /**
     * Define properties that are automatically converted to Time instances.
     */
    // protected $dates = ['created_at', 'updated_at', 'deleted_at', 'expiry'];
    protected $dates = ['created_at', 'expiry'];

    /**
     * Array of field names and the type of value to cast them as
     * when they are accessed.
     */
    protected $casts = [];
}
