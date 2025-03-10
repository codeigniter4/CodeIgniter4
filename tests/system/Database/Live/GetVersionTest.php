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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class GetVersionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;

    public function testGetVersion(): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $this->db->mysqli = false;
        }

        $this->db->connID = false;

        $version = $this->db->getVersion();

        $this->assertMatchesRegularExpression('/\A\d+(\.\d+)*\z/', $version);
    }
}
