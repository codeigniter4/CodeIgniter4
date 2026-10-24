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

class CustomEnvHookConfig extends BaseConfig
{
    public string $name   = 'default';
    public string $hooked = 'default';

    protected function getEnvValue(string $property, string $prefix, string $shortPrefix)
    {
        if ($property === 'hooked') {
            return 'from-hook';
        }

        return parent::getEnvValue($property, $prefix, $shortPrefix);
    }
}
