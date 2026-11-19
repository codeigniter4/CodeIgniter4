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

use CodeIgniter\Database\Query;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Override;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class PretendTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    #[Override]
    protected function tearDown(): void
    {
        // We share `$this->db` in testing, so we need to restore the state.
        $this->db->pretend(false);
    }

    public function testPretendReturnsQueryObject(): void
    {
        $result = $this->db->pretend(false)
            ->table('user')
            ->get();

        $this->assertNotInstanceOf(Query::class, $result);

        $result = $this->db->pretend(true)
            ->table('user')
            ->get();

        $this->assertInstanceOf(Query::class, $result);
    }

    public function testPrepareResetsPretendWhenClosureThrowsException(): void
    {
        $this->db->pretend(false);

        $exception = new RuntimeException('Error in prepare closure');

        try {
            $this->db->prepare(static function () use ($exception): never {
                throw $exception;
            });

            $this->fail('prepare() must rethrow the exception thrown by the closure.');
        } catch (RuntimeException $e) {
            $this->assertSame($exception, $e, 'prepare() must rethrow the exception thrown by the closure.');
        }

        $this->assertFalse($this->db->pretend, 'prepare() must reset pretend mode when the closure throws.');
    }
}
