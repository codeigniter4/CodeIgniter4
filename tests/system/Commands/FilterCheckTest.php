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

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FilterCheckTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->resetServices();
        parent::tearDown();
    }

    protected function getBuffer()
    {
        return $this->getStreamFilterBuffer();
    }

    public function testFilterCheckDefinedRoute(): void
    {
        command('filter:check GET /');

        $this->assertStringContainsString(
            '| GET    | /     | forcehttps pagecache | pagecache performance toolbar |',
            preg_replace('/\033\[.+?m/u', '', $this->getBuffer()),
        );
    }

    public function testFilterCheckInvalidRoute(): void
    {
        command('filter:check PUT product/123');

        $this->assertStringContainsString(
            'Can\'t find a route: "PUT product/123"',
            str_replace(
                ["\033[0m", "\033[1;31m", "\033[0;30m", "\033[47m"],
                '',
                $this->getBuffer(),
            ),
        );
    }
}
