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

use CodeIgniter\Events\Events;

/**
 * Events
 */
class MockEvents extends Events
{
    public function getListeners()
    {
        return self::$listeners;
    }

    public function getEventsFile()
    {
        return self::$files;
    }

    public function getSimulate()
    {
        return self::$simulate;
    }

    public function unInitialize()
    {
        static::$initialized = false;
    }
}
