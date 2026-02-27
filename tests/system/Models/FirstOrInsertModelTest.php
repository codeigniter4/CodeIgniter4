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
use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;
use CodeIgniter\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use ReflectionProperty;
use stdClass;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class FirstOrInsertModelTest extends LiveModelTestCase
{
    protected function tearDown(): void
    {
        $this->enableDBDebug();
        parent::tearDown();
    }

    public function testReturnsExistingRecord(): void
    {
        $this->createModel(UserModel::class);

        $row = $this->model->firstOrInsert(['email' => 'derek@world.com']);

        $this->assertIsObject($row);
        $this->assertSame('Derek Jones', $row->name);
        $this->assertSame('derek@world.com', $row->email);
        $this->assertSame('US', $row->country);
    }

    public function testDoesNotInsertWhenRecordExists(): void
    {
        $this->createModel(UserModel::class);

        $this->model->firstOrInsert(['email' => 'derek@world.com']);

        // Seeder inserts 4 users; calling firstOrInsert on an existing
        // record must not add a fifth one.
        $this->seeNumRecords(4, 'user', ['deleted_at' => null]);
    }

    public function testValuesAreIgnoredWhenRecordExists(): void
    {
        $this->createModel(UserModel::class);

        // The $values array must not be used to modify the found record.
        $row = $this->model->firstOrInsert(
            ['email' => 'derek@world.com'],
            ['name' => 'Should Not Change', 'country' => 'XX'],
        );

        $this->assertIsObject($row);
        $this->assertSame('Derek Jones', $row->name);
        $this->assertSame('US', $row->country);
    }

    public function testInsertsNewRecordWhenNotFound(): void
    {
        $this->createModel(UserModel::class);

        $row = $this->model->firstOrInsert([
            'name'    => 'New User',
            'email'   => 'new@example.com',
            'country' => 'US',
        ]);

        $this->assertIsObject($row);
        $this->assertSame('new@example.com', $row->email);
        $this->seeInDatabase('user', ['email' => 'new@example.com', 'deleted_at' => null]);
    }

    public function testMergesValuesOnInsert(): void
    {
        $this->createModel(UserModel::class);

        $row = $this->model->firstOrInsert(
            ['email' => 'new@example.com'],
            ['name' => 'New User', 'country' => 'CA'],
        );

        $this->assertIsObject($row);
        $this->assertSame('New User', $row->name);
        $this->assertSame('CA', $row->country);
        $this->seeInDatabase('user', [
            'email'      => 'new@example.com',
            'name'       => 'New User',
            'country'    => 'CA',
            'deleted_at' => null,
        ]);
    }

    public function testAcceptsObjectForValues(): void
    {
        $this->createModel(UserModel::class);

        $values          = new stdClass();
        $values->name    = 'Object User';
        $values->country = 'DE';

        $row = $this->model->firstOrInsert(
            ['email' => 'object@example.com'],
            $values,
        );

        $this->assertIsObject($row);
        $this->assertSame('Object User', $row->name);
        $this->assertSame('DE', $row->country);
        $this->seeInDatabase('user', ['email' => 'object@example.com', 'deleted_at' => null]);
    }

    public function testAcceptsObjectForAttributes(): void
    {
        $this->createModel(UserModel::class);

        $attributes        = new stdClass();
        $attributes->email = 'derek@world.com';

        $row = $this->model->firstOrInsert($attributes);

        $this->assertIsObject($row);
        $this->assertSame('Derek Jones', $row->name);
        $this->seeNumRecords(4, 'user', ['deleted_at' => null]);
    }

    public function testAcceptsObjectForAttributesAndInsertsWhenNotFound(): void
    {
        $this->createModel(UserModel::class);

        $attributes          = new stdClass();
        $attributes->email   = 'new@example.com';
        $attributes->name    = 'New User';
        $attributes->country = 'US';

        $row = $this->model->firstOrInsert($attributes);

        $this->assertIsObject($row);
        $this->assertSame('new@example.com', $row->email);
        $this->seeInDatabase('user', ['email' => 'new@example.com', 'deleted_at' => null]);
    }

    public function testThrowsOnEmptyAttributes(): void
    {
        $this->createModel(UserModel::class);

        $this->expectException(InvalidArgumentException::class);
        $this->model->firstOrInsert([]);
    }

    public function testHandlesRaceConditionWithDebugEnabled(): void
    {
        // Subclass that simulates a concurrent insert winning the race:
        // doInsert() first persists the row (the "other process"), then
        // throws UniqueConstraintViolationException as if our own attempt
        // also tried to insert the same row.
        $model = new class ($this->db) extends UserModel {
            protected function doInsert(array $row): bool
            {
                parent::doInsert($row);

                throw new UniqueConstraintViolationException('Duplicate entry');
            }
        };

        $row = $model->firstOrInsert(
            ['email' => 'race@example.com'],
            ['name' => 'Race User', 'country' => 'US'],
        );

        $this->assertIsObject($row);
        $this->assertSame('race@example.com', $row->email);
        // The "other process" inserted exactly one record.
        $this->seeNumRecords(1, 'user', ['email' => 'race@example.com', 'deleted_at' => null]);
    }

    public function testHandlesRaceConditionWithDebugDisabled(): void
    {
        $this->disableDBDebug();

        // Subclass that simulates a concurrent insert: the "other process"
        // inserts via a direct DB call, then our own attempt fails with a
        // unique violation which is stored in lastException (DBDebug=false).
        $model = new class ($this->db) extends UserModel {
            protected function doInsert(array $row): bool
            {
                // Direct insert – bypasses the model so it won't interfere
                // with the model's own builder state.
                $this->db->table($this->table)->insert([
                    'name'    => $row['name'],
                    'email'   => $row['email'],
                    'country' => $row['country'],
                ]);

                // The real insert now fails; the driver stores
                // UniqueConstraintViolationException in lastException.
                return parent::doInsert($row);
            }
        };

        $row = $model->firstOrInsert(
            ['email' => 'race@example.com'],
            ['name' => 'Race User', 'country' => 'US'],
        );

        $this->assertIsObject($row);
        $this->assertSame('race@example.com', $row->email);
        $this->seeNumRecords(1, 'user', ['email' => 'race@example.com', 'deleted_at' => null]);
    }

    public function testReturnsFalseOnNonUniqueErrorWithDebugDisabled(): void
    {
        $this->disableDBDebug();

        // Subclass that simulates a non-unique database error by placing
        // a plain DatabaseException (not UniqueConstraintViolationException)
        // into lastException and returning false.
        $model = new class ($this->db) extends UserModel {
            protected function doInsert(array $row): bool
            {
                $prop = new ReflectionProperty($this->db, 'lastException');
                $prop->setValue($this->db, new DatabaseException('Connection error'));

                return false;
            }
        };

        $result = $model->firstOrInsert(
            ['email' => 'error@example.com'],
            ['name' => 'Error User', 'country' => 'US'],
        );

        $this->assertFalse($result);
        $this->dontSeeInDatabase('user', ['email' => 'error@example.com']);
    }

    public function testReturnsFalseOnValidationFailure(): void
    {
        // Subclass with strict validation rules that the test data fails.
        $model = new class ($this->db) extends UserModel {
            protected $validationRules = [
                'email' => 'required|valid_email',
                'name'  => 'required|min_length[50]',
            ];
        };

        $result = $model->firstOrInsert(
            ['email' => 'not-a-valid-email'],
            ['name' => 'Too Short'],
        );

        $this->assertFalse($result);
        $this->dontSeeInDatabase('user', ['email' => 'not-a-valid-email']);
        $this->assertNotEmpty($model->errors());
    }
}
