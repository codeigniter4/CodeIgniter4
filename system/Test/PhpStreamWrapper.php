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

namespace CodeIgniter\Test;

/**
 * StreamWrapper for php protocol
 *
 * This class is used for mocking `php://stdin`.
 *
 * See https://www.php.net/manual/en/class.streamwrapper.php
 */
final class PhpStreamWrapper
{
    /**
     * @var resource|null
     */
    public $context;

    private static string $content = '';
    private int $position          = 0;

    public static function setContent(string $content)
    {
        self::$content = $content;
    }

    public static function register()
    {
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', self::class);
    }

    public static function restore()
    {
        stream_wrapper_restore('php');
    }

    public function stream_open(string $path): bool
    {
        return true;
    }

    /**
     * @return false|string
     */
    public function stream_read(int $count)
    {
        $return = substr(self::$content, $this->position, $count);
        $this->position += strlen($return);

        return $return;
    }

    /**
     * @return array|false
     */
    public function stream_stat()
    {
        return [];
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen(self::$content);
    }
}
