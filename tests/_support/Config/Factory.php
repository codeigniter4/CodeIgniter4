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

use CodeIgniter\Config\Factory as FactoryConfig;

/**
 * Factories Configuration file.
 *
 * Provides overriding directives for how
 * Factories should handle discovery and
 * instantiation of specific components.
 * Each property should correspond to the
 * lowercase, plural component name.
 */
class Factory extends FactoryConfig
{
    public $widgets = ['bar' => 'bam'];
}
