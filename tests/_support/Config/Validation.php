<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Config;

use Config\Validation as ValidationConfig;

class Validation extends ValidationConfig
{
    public $signup = [
        'id'   => 'permit_empty|is_natural_no_zero',
        'name' => [
            'required',
            'min_length[3]',
        ],
        'token' => 'permit_empty|in_list[{id}]',
    ];
    public $signup_errors = [
        'name' => [
            'required'   => 'You forgot to name the baby.',
            'min_length' => 'Too short, man!',
        ],
    ];
}
