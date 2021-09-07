<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use Config\Autoload;

class MockAutoload extends Autoload
{
    public $psr4 = [];

    public $classmap = [];

    public function __construct()
    {
        // Don't call the parent since we don't want the default mappings.
        // parent::__construct();
    }
}
