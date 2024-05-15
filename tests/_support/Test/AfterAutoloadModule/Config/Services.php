<?php
namespace AfterAutoloadModule\Config;

use CodeIgniter\Config\BaseService;
use AfterAutoloadModule\Test;

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
class Services extends BaseService
{
    public static function test(bool $getShared = true) {
        if ($getShared) {
            return static::getSharedInstance('test');
        }

        return new Test();
    }
}