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

use CodeIgniter\Config\BaseConfig;

/**
 * Adds an array leaf only when $grow is set, like a constructor or registrar
 * that changes the shape between instances.
 */
class GrowingEnvConfig extends BaseConfig
{
    public static bool $grow = false;

    /**
     * @var array<string, string>
     */
    public array $list = ['key' => 'default'];

    public function __construct()
    {
        if (self::$grow) {
            $this->list['added'] = 'default';
        }

        parent::__construct();
    }
}
