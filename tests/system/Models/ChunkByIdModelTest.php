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

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Model;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ChunkByIdModelTest extends LiveModelTestCase
{
    public function testChunkById(): void
    {
        $ids = [];

        $this->createModel(UserModel::class)->chunkById(2, static function ($row) use (&$ids): void {
            $ids[] = self::userId($row);
        });

        $this->assertSame([1, 2, 3, 4], $ids);
    }

    public function testChunkByIdThrowsOnZeroSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$size must be a positive integer.');

        $this->createModel(UserModel::class)->chunkById(0, static function ($row): void {});
    }

    public function testChunkByIdThrowsOnNegativeSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$size must be a positive integer.');

        $this->createModel(UserModel::class)->chunkById(-1, static function ($row): void {});
    }

    public function testChunkByIdEarlyExit(): void
    {
        $ids = [];

        $this->createModel(UserModel::class)->chunkById(2, static function ($row) use (&$ids): bool {
            $ids[] = self::userId($row);

            return false;
        });

        $this->assertSame([1], $ids);
    }

    public function testChunkByIdRespectsBuilderConditions(): void
    {
        $ids = [];

        $this->createModel(UserModel::class)
            ->where('country', 'US')
            ->chunkById(2, static function ($row) use (&$ids): void {
                $ids[] = self::userId($row);
            });

        $this->assertSame([1, 3], $ids);
    }

    public function testChunkByIdDoesNotSkipRowsWhenProcessedRowsAreDeleted(): void
    {
        $ids = [];

        $this->createModel(UserModel::class)->chunkById(2, function ($row) use (&$ids): void {
            $id    = self::userId($row);
            $ids[] = $id;

            if ($id === 1) {
                $this->db->table('user')->where('id', 1)->delete();
            }
        });

        $this->assertSame([1, 2, 3, 4], $ids);
    }

    public function testChunkByIdPreservesConditionsWhenSameModelDeletesRows(): void
    {
        $ids = [];
        $this->createModel(UserModel::class);

        $this->model
            ->where('country', 'US')
            ->chunkById(1, function ($row) use (&$ids): void {
                $id    = self::userId($row);
                $ids[] = $id;

                $this->model->delete($id);
            });

        $this->assertSame([1, 3], $ids);
    }

    public function testChunkByIdRespectsSoftDeletes(): void
    {
        $ids = [];
        $this->createModel(UserModel::class);

        $this->model->delete(1);
        $this->model->chunkById(2, static function ($row) use (&$ids): void {
            $ids[] = self::userId($row);
        });

        $this->assertSame([2, 3, 4], $ids);
    }

    public function testChunkByIdWithDeleted(): void
    {
        $ids = [];
        $this->createModel(UserModel::class);

        $this->model->delete(1);
        $this->model->withDeleted()->chunkById(2, static function ($row) use (&$ids): void {
            $ids[] = self::userId($row);
        });

        $this->assertSame([1, 2, 3, 4], $ids);
    }

    public function testChunkByIdOnlyDeleted(): void
    {
        $ids = [];
        $this->createModel(UserModel::class);

        $this->model->delete(1);
        $this->model->onlyDeleted()->chunkById(2, static function ($row) use (&$ids): void {
            $ids[] = self::userId($row);
        });

        $this->assertSame([1], $ids);
    }

    public function testChunkByIdThrowsWithOrderBy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID-based chunking cannot be used with orderBy().');

        $this->createModel(UserModel::class)
            ->orderBy('name')
            ->chunkById(2, static function ($row): void {});
    }

    public function testChunkByIdThrowsWithGroupBy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID-based chunking cannot be used with groupBy().');

        $this->createModel(UserModel::class)
            ->groupBy('country')
            ->chunkById(2, static function ($row): void {});
    }

    public function testChunkByIdThrowsWithLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID-based chunking cannot be used with limit(), offset() or union().');

        $this->createModel(UserModel::class)
            ->limit(2)
            ->chunkById(2, static function ($row): void {});
    }

    public function testChunkByIdThrowsWithOffset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID-based chunking cannot be used with limit(), offset() or union().');

        $this->createModel(UserModel::class)
            ->offset(1)
            ->chunkById(2, static function ($row): void {});
    }

    public function testChunkByIdThrowsWithUnion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID-based chunking cannot be used with limit(), offset() or union().');

        $model = $this->createModel(UserModel::class);
        $model->builder()->union(static fn (BaseBuilder $builder): BaseBuilder => $builder->from('user'));
        $model->chunkById(2, static function ($row): void {});
    }

    public function testChunkByIdThrowsWhenPrimaryKeyIsNotSelected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The primary key must be selected for ID-based chunking.');

        $this->createModel(UserModel::class)
            ->select('name')
            ->chunkById(2, static function ($row): void {});
    }

    public function testChunkByIdThrowsWithoutPrimaryKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID-based chunking requires a primary key.');

        $model = new class ($this->db) extends Model {
            protected $table      = 'user';
            protected $primaryKey = '';
            protected $returnType = 'object';
        };

        $model->chunkById(2, static function ($row): void {});
    }

    public function testChunkRowsById(): void
    {
        $chunkCount     = 0;
        $numRowsInChunk = [];

        $this->createModel(UserModel::class)->chunkRowsById(2, static function ($rows) use (&$chunkCount, &$numRowsInChunk): void {
            $chunkCount++;
            $numRowsInChunk[] = count($rows);
        });

        $this->assertSame(2, $chunkCount);
        $this->assertSame([2, 2], $numRowsInChunk);
    }

    public function testChunkRowsByIdEarlyExit(): void
    {
        $chunkCount = 0;

        $this->createModel(UserModel::class)->chunkRowsById(2, static function ($rows) use (&$chunkCount): bool {
            $chunkCount++;

            return false;
        });

        $this->assertSame(1, $chunkCount);
    }

    public function testChunkRowsByIdThrowsOnZeroSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$size must be a positive integer.');

        $this->createModel(UserModel::class)->chunkRowsById(0, static function ($rows): void {});
    }

    /**
     * @param array<string, mixed>|object $row
     */
    private static function userId(array|object $row): int
    {
        $data = (array) $row;

        if (! array_key_exists('id', $data)) {
            self::fail('Expected the row to contain an id value.');
        }

        return (int) $data['id'];
    }
}
