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
use function stream_bucket_make_writeable;
use function stream_filter_register;
use const PSFS_PASS_ON;

/**
 * Class to extract an output snapshot.
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
     * Output filtering - catch it all.
     *
     * @param resource $in
     * @param resource $out
     * @param int      $consumed
     * @param bool     $closing
     *
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            static::$buffer .= $bucket->data;

            $consumed += $bucket->datalen;
        }

        // @phpstan-ignore-next-line
        return PSFS_PASS_ON;
    }
}

// @codeCoverageIgnoreStart
stream_filter_register('CITestStreamFilter', 'CodeIgniter\Test\Filters\CITestStreamFilter');
// @codeCoverageIgnoreEnd
