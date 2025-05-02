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

    protected static bool $registered = false;

    /**
     * @var resource|null
     */
    private static $err;

    /**
     * @var resource|null
     */
    private static $out;

    /**
     * This method is called whenever data is read from or written to the
     * attached stream (such as with fread() or fwrite()).
     *
     * @param resource $in
     * @param resource $out
     * @param int      $consumed
     * @param bool     $closing
     *
     * @param-out int $consumed
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            static::$buffer .= $bucket->data;
            $consumed += (int) $bucket->datalen;
        }

        return PSFS_PASS_ON;
    }

    public static function registration(): void
    {
        if (! static::$registered) {
            static::$registered = stream_filter_register('CITestStreamFilter', self::class); // @codeCoverageIgnore
        }

        static::$buffer = '';
    }

    public static function addErrorFilter(): void
    {
        self::removeFilter(self::$err);
        self::$err = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    public static function addOutputFilter(): void
    {
        self::removeFilter(self::$out);
        self::$out = stream_filter_append(STDOUT, 'CITestStreamFilter');
    }

    public static function removeErrorFilter(): void
    {
        self::removeFilter(self::$err);
    }

    public static function removeOutputFilter(): void
    {
        self::removeFilter(self::$out);
    }

    /**
     * @param resource|null $stream
     *
     * @param-out null $stream
     */
    protected static function removeFilter(&$stream): void
    {
        if ($stream !== null) {
            stream_filter_remove($stream);
            $stream = null;
        }
    }
}
