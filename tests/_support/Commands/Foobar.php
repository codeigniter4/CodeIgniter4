<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Config\App;
use CodeIgniter\CLI\CLI;

return [
    'foo' => 'The command will use this as foo.',
    'bar' => 'The command will use this as bar.',
    'baz' => 'The baz is here.',
    'bas' => CLI::color('bas', 'green') . (new App())->baseURL,
];
