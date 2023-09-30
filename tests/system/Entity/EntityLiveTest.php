<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use stdClass;

/**
 * @internal
 *
 * @group DatabaseLive
 */
final class EntityLiveTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'Tests\Support\Entity';

    protected function setUp(): void
    {
        $this->setUpMethods[] = 'setUpAddNamespace';

        parent::setUp();
    }

    protected function setUpAddNamespace(): void
    {
        Services::autoloader()->addNamespace(
            'Tests\Support\Entity',
            SUPPORTPATH . 'Entity'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->regressDatabase();
    }

    public function testEntityReturnsValuesWithCorrectTypes()
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'id'     => 'int',
                'active' => 'int-bool',
                'memo'   => 'json',
            ];
        };
        $model = new class () extends Model {
            protected $table         = 'users';
            protected $allowedFields = [
                'username', 'active', 'memo',
            ];
            protected $useTimestamps = true;
        };
        $entity->fill(['username' => 'johnsmith', 'active' => false, 'memo' => ['foo', 'bar']]);
        $model->save($entity);

        $user = $model->asObject(get_class($entity))->find(1);

        $this->assertSame(1, $user->id);
        $this->assertSame('johnsmith', $user->username);
        $this->assertFalse($user->active);
        $this->assertSame(['foo', 'bar'], $user->memo);
        $this->assertInstanceOf(Time::class, $user->created_at);
        $this->assertInstanceOf(Time::class, $user->updated_at);
    }

    /**
     * @TODO Fix the object cast handler implementation.
     */
    public function testCastObject(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'id'     => 'int',
                'active' => 'int-bool',
                'memo'   => 'object',
            ];
        };
        $model = new class () extends Model {
            protected $table         = 'users';
            protected $allowedFields = [
                'username', 'active', 'memo',
            ];
            protected $useTimestamps = true;
        };
        $entity->fill(['username' => 'johnsmith', 'active' => false, 'memo' => ['foo', 'bar']]);
        $model->save($entity);

        $user = $model->asObject(get_class($entity))->find(1);

        $this->assertInstanceOf(stdClass::class, $user->memo);
        $this->assertSame(['foo', 'bar'], (array) $user->memo);
    }
}
