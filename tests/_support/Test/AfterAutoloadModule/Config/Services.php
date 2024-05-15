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

namespace AfterAutoloadModule\Config;

use AfterAutoloadModule\Test;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function test(bool $getShared = true): Test
    {
        if ($getShared) {
            return static::getSharedInstance('test');
        }

        return new Test();
    }
}
