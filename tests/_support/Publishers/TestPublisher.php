<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Publishers;

use CodeIgniter\Publisher\Publisher;

final class TestPublisher extends Publisher
{
    /**
     * Return value for publish()
     *
     * @var bool
     */
    private static $result = true;

    /**
     * Base path to use for the source.
     *
     * @var string
     */
    protected $source = SUPPORTPATH . 'Files';

    /**
     * Base path to use for the destination.
     *
     * @var string
     */
    protected $destination = WRITEPATH;

    /**
     * Fakes an error on the given file.
     */
    public static function setResult(bool $result)
    {
        self::$result = $result;
    }

    /**
     * Fakes a publish event so no files are actually copied.
     */
    public function publish(): bool
    {
        $this->addPath('');

        return self::$result;
    }
}
