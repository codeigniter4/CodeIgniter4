<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Support\Cache;

use CodeIgniter\Cache\Handlers\DummyHandler;

/**
 * Handler with unnecessarily restrictive
 * key limit for testing validateKey.
 */
class RestrictiveHandler extends DummyHandler
{
    /**
     * Maximum key length.
     */
    public const MAX_KEY_LENGTH = 10;
}
