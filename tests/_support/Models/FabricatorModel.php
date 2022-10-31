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
use Faker\Generator;

class FabricatorModel extends Model
{
    protected $table          = 'job';
    protected $returnType     = 'object';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'int';
    protected $allowedFields  = [
        'name',
        'description',
    ];

    // Return a faked entity
    public function fake(Generator &$faker)
    {
        return (object) [
            'name'        => $faker->ipv4(),
            'description' => $faker->words(10),
        ];
    }
}
