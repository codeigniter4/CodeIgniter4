<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Filters;

use php_user_filter;

/**
 * Used to capture output during unit testing, so that it can
 * be used in assertions.
 */
class CITestStreamFilter extends php_user_filter
{
    /**
     * Buffer to capture stream content.
     *
     * @var string
     */
    public static $buffer = '';

    /**
     * This method is called whenever data is read from or written to the
     * attached stream (such as with fread() or fwrite()).
     *
     * @param resource $in
     * @param resource $out
     * @param int      $consumed
     * @param bool     $closing
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            static::$buffer .= $bucket->data;

            $consumed += $bucket->datalen;
        }

        return PSFS_PASS_ON;
    }
}

stream_filter_register('CITestStreamFilter', CITestStreamFilter::class); // @codeCoverageIgnore
