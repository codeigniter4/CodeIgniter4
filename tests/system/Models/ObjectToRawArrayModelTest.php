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

use CodeIgniter\Entity\Entity;
use CodeIgniter\Model;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ObjectToRawArrayModelTest extends CIUnitTestCase
{
    private function createModel(): Model
    {
        return new class () extends Model {
            public function __construct()
            {
                // Skip DB connection — we only test objectToRawArray
            }

            protected $table          = 'test';
            protected $allowedFields  = ['name', 'nested', 'entity'];
            protected $returnType     = 'array';
            protected $useSoftDeletes = false;
        };
    }

    /**
     * Call protected objectToRawArray via reflection.
     *
     * @return array<string, mixed>
     */
    private function callObjectToRawArray(Model $model, object $object, bool $onlyChanged, bool $recursive): array
    {
        $method = self::getPrivateMethodInvoker($model, 'objectToRawArray');

        return $method($object, $onlyChanged, $recursive);
    }

    public function testObjectToRawArrayPassesRecursiveTrue(): void
    {
        $model = $this->createModel();

        $inner = new class () extends Entity {
            protected $attributes = ['name' => 'inner'];
            protected $original   = ['name' => 'inner'];
        };

        $outer = new class () extends Entity {
            protected $attributes = ['name' => 'outer', 'nested' => null];
            protected $original   = ['name' => 'outer', 'nested' => null];
        };
        $outer->nested = $inner;

        $result = $this->callObjectToRawArray($model, $outer, false, true);

        $this->assertArrayHasKey('name', $result);
        $this->assertSame('outer', $result['name']);
        $this->assertArrayHasKey('nested', $result);
        $this->assertIsArray($result['nested']);
        $this->assertSame(['name' => 'inner'], $result['nested']);
    }

    public function testObjectToRawArrayPassesRecursiveFalse(): void
    {
        $model = $this->createModel();

        $inner = new class () extends Entity {
            protected $attributes = ['name' => 'inner'];
            protected $original   = ['name' => 'inner'];
        };

        $outer = new class () extends Entity {
            protected $attributes = ['name' => 'outer', 'nested' => null];
            protected $original   = ['name' => 'outer', 'nested' => null];
        };
        $outer->nested = $inner;

        $result = $this->callObjectToRawArray($model, $outer, false, false);

        $this->assertArrayHasKey('name', $result);
        $this->assertSame('outer', $result['name']);
        $this->assertArrayHasKey('nested', $result);
        // With recursive=false, nested Entity should remain as object
        $this->assertInstanceOf(Entity::class, $result['nested']);
    }

    public function testObjectToRawArrayNonEntity(): void
    {
        $model = $this->createModel();

        $obj = new class () {
            public string $name  = 'test';
            public string $value = '123';
        };

        $result = $this->callObjectToRawArray($model, $obj, false, false);

        $this->assertSame(['name' => 'test', 'value' => '123'], $result);
    }

    public function testObjectToRawArrayOnlyChanged(): void
    {
        $model  = $this->createModel();
        $entity = new class () extends Entity {
            protected $attributes = ['name' => 'original', 'value' => 'keep'];
            protected $original   = ['name' => 'original', 'value' => 'keep'];
        };
        $entity->name = 'modified';

        $result = $this->callObjectToRawArray($model, $entity, true, false);

        $this->assertSame(['name' => 'modified'], $result);
    }
}
