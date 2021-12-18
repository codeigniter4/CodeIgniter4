<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Config\Services;

// CodeIgniter Security Helpers

if (! function_exists('sanitize_filename')) {
    /**
     * Sanitize a filename to use in a URI.
     */
    function sanitize_filename(string $filename): string
    {
        return Services::security()->sanitizeFilename($filename);
    }
}

if (! function_exists('strip_image_tags')) {
    /**
     * Strip Image Tags
     */
    function strip_image_tags(string $str): string
    {
        return preg_replace(
            [
                '#<img[\s/]+.*?src\s*=\s*(["\'])([^\\1]+?)\\1.*?\>#i',
                '#<img[\s/]+.*?src\s*=\s*?(([^\s"\'=<>`]+)).*?\>#i',
            ],
            '\\2',
            $str
        );
    }
}

if (! function_exists('encode_php_tags')) {
    /**
     * Convert PHP tags to entities
     */
    function encode_php_tags(string $str): string
    {
        return str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $str);
    }
}
