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

use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class WithLockedRowModelTest extends LiveModelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 does not support lockForUpdate().');
        }
    }

    public function testWithLockedRowReturnsCallbackResult(): void
    {
        $inTransaction = false;
        $model         = $this->createModel(UserModel::class);

        $result = $model->withLockedRow(1, static function (object $user, UserModel $model) use (&$inTransaction): string {
            $inTransaction = $model->db->inTransaction();

            return $user->email;
        });

        $this->assertSame('derek@world.com', $result);
        $this->assertTrue($inTransaction);
        $this->assertFalse($this->db->inTransaction());
    }

    public function testWithLockedRowDoesNotRunCallbackWhenRowIsMissing(): void
    {
        $callbackRan = false;
        $model       = $this->createModel(UserModel::class);

        $result = $model->withLockedRow(999, static function () use (&$callbackRan): void {
            $callbackRan = true;
        });

        $this->assertNull($result);
        $this->assertFalse($callbackRan);
    }

    public function testWithLockedRowAppliesExistingQueryConstraints(): void
    {
        $model = $this->createModel(UserModel::class);

        $result = $model->where('country', 'CA')->withLockedRow(1, static fn (): string => 'locked');

        $this->assertNull($result);
    }

    public function testWithLockedRowRespectsSoftDeletes(): void
    {
        $model = $this->createModel(UserModel::class);
        $model->delete(1);

        $result = $model->withLockedRow(1, static fn (): string => 'locked');

        $this->assertNull($result);
    }

    public function testWithLockedRowCanIncludeSoftDeletedRows(): void
    {
        $model = $this->createModel(UserModel::class);
        $model->delete(1);

        $result = $model->withDeleted()->withLockedRow(1, static fn (object $user): string => $user->email);

        $this->assertSame('derek@world.com', $result);
    }

    public function testWithLockedRowRollsBackWhenCallbackThrows(): void
    {
        $model = $this->createModel(UserModel::class);

        try {
            $model->withLockedRow(1, static function (object $user, UserModel $model): void {
                $model->update($user->id, ['name' => 'Rolled Back']);

                throw new RuntimeException('Stop transaction.');
            });
        } catch (RuntimeException $e) {
            $this->assertSame('Stop transaction.', $e->getMessage());
        }

        $this->seeInDatabase('user', [
            'id'   => 1,
            'name' => 'Derek Jones',
        ]);
    }

    public function testWithLockedRowBypassesFindCallbacks(): void
    {
        $model                       = $this->createModel(EventModel::class);
        $model->beforeFindReturnData = true;

        $result = $model->withLockedRow(1, static fn (array $user): string => $user['email']);

        $this->assertSame('derek@world.com', $result);
        $this->assertFalse($model->hasToken('beforeFind'));
        $this->assertFalse($model->hasToken('afterFind'));
    }

    public function testWithLockedRowRestoresCallbacksBeforeRunningCallback(): void
    {
        $model = $this->createModel(EventModel::class);

        $model->withLockedRow(1, static function (array $user, EventModel $model): void {
            $model->update($user['id'], ['name' => 'Locked Update']);
        });

        $this->assertTrue($model->hasToken('beforeUpdate'));
        $this->assertTrue($model->hasToken('afterUpdate'));
    }

    public function testWithLockedRowDoesNotAddLimitToLockedLookup(): void
    {
        $model = $this->createModel(UserModel::class);

        $model->withLockedRow(1, static fn (): string => 'locked');

        $sql = strtoupper((string) $this->db->getLastQuery());

        $this->assertStringNotContainsString(' LIMIT ', $sql);
        $this->assertStringNotContainsString(' OFFSET ', $sql);
    }
}
