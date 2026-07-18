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

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\WithLockedRowConnection;

/**
 * @internal
 */
#[Group('Others')]
final class WithLockedRowUnitTest extends CIUnitTestCase
{
    public function testWithLockedRowReturnsCallbackResultInsideTransactionAndLocksQuery(): void
    {
        $db    = new WithLockedRowConnection([['id' => 1, 'email' => 'derek@world.com']]);
        $model = new UserModel($db);

        $inTransaction = false;
        $result        = $model->withLockedRow(1, static function (object $user, UserModel $model) use (&$inTransaction): string {
            $inTransaction = $model->db->inTransaction();

            return $user->email;
        });

        $sql = (string) $db->getLastQuery();

        $this->assertSame('derek@world.com', $result);
        $this->assertTrue($inTransaction);
        $this->assertFalse($db->inTransaction());
        $this->assertStringContainsString('FOR UPDATE', $sql);
        $this->assertStringNotContainsString('LIMIT', strtoupper($sql));
        $this->assertStringNotContainsString('OFFSET', strtoupper($sql));
    }

    public function testWithLockedRowReturnsNullWithoutRunningCallbackWhenRowIsMissing(): void
    {
        $model       = new UserModel(new WithLockedRowConnection());
        $callbackRan = false;

        $result = $model->withLockedRow(1, static function () use (&$callbackRan): void {
            $callbackRan = true;
        });

        $this->assertNull($result);
        $this->assertFalse($callbackRan);
    }

    public function testWithLockedRowBypassesFindCallbacks(): void
    {
        $model                       = new EventModel(new WithLockedRowConnection([['id' => 1, 'email' => 'derek@world.com']]));
        $model->beforeFindReturnData = true;

        $result = $model->withLockedRow(1, static fn (array $user): string => $user['email']);

        $this->assertSame('derek@world.com', $result);
        $this->assertFalse($model->hasToken('beforeFind'));
        $this->assertFalse($model->hasToken('afterFind'));
    }

    public function testWithLockedRowRestoresCallbacksBeforeRunningCallback(): void
    {
        $model = new EventModel(new WithLockedRowConnection([['id' => 1, 'email' => 'derek@world.com']]));

        $model->withLockedRow(1, static function (array $user, EventModel $model): void {
            $model->update($user['id'], ['name' => 'Locked Update']);
        });

        $this->assertTrue($model->hasToken('beforeUpdate'));
        $this->assertTrue($model->hasToken('afterUpdate'));
    }

    public function testWithLockedRowRollsBackWhenCallbackThrows(): void
    {
        $db    = new WithLockedRowConnection([['id' => 1, 'email' => 'derek@world.com']]);
        $model = new UserModel($db);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stop transaction.');

        try {
            $model->withLockedRow(1, static function (): void {
                throw new RuntimeException('Stop transaction.');
            });
        } finally {
            $this->assertFalse($db->inTransaction());
        }
    }

    public function testWithLockedRowCleansUpModelStateWhenLockedLookupThrows(): void
    {
        $db    = new WithLockedRowConnection([['id' => 1, 'email' => 'derek@world.com']], true);
        $model = new UserModel($db);

        try {
            $model->where('country', 'US')->withDeleted()->withLockedRow(1, static function (): void {
            });

            $this->fail('Expected locked lookup to throw.');
        } catch (DatabaseException $e) {
            $this->assertSame('Locked lookup failed.', $e->getMessage());
        }

        $db->throwOnSelect = false;

        $result = $model->withLockedRow(1, static fn (object $user): string => $user->email);
        $sql    = (string) $db->getLastQuery();

        $this->assertSame('derek@world.com', $result);
        $this->assertStringContainsString('FOR UPDATE', $sql);
        $this->assertStringNotContainsString('country', $sql);
    }
}
