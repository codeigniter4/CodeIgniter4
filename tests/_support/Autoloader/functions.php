<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

if (! function_exists('autoload_foo')) {
    function autoload_foo(): string
    {
        return 'I am autoloaded by Autoloader through $files!';
    }
}

if (! defined('AUTOLOAD_CONSTANT')) {
    define('AUTOLOAD_CONSTANT', 'foo');
}
