<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Fabricator;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class FabricatorLiveTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    public function testCreateAddsToDatabase(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        // Some countries violate the 40 character limit so override that
        $fabricator->setOverrides(['country' => 'Spain']);

        $result = $fabricator->create();

        $this->seeInDatabase('user', ['name' => $result->name]);
    }

    public function testCreateAddsCountToDatabase(): void
    {
        $count = 10;

        $fabricator = new Fabricator(UserModel::class);

        // Some countries violate the 40 character limit so override that
        $fabricator->setOverrides(['country' => 'France']);

        $fabricator->create($count);

        $this->seeNumRecords($count, 'user', []);
    }

    public function testHelperCreates(): void
    {
        helper('test');

        $result = fake(UserModel::class, ['country' => 'Italy']);

        $this->seeInDatabase('user', ['name' => $result->name]);
    }

    public function testCreateIncrementsCount(): void
    {
        $fabricator = new Fabricator(UserModel::class);
        $fabricator->setOverrides(['country' => 'China']);

        $count = Fabricator::getCount('user');

        $fabricator->create();

        $this->assertSame($count + 1, Fabricator::getCount('user'));
    }

    public function testHelperIncrementsCount(): void
    {
        $count = Fabricator::getCount('user');

        fake(UserModel::class, ['country' => 'Italy']);

        $this->assertSame($count + 1, Fabricator::getCount('user'));
    }

    public function testCreateThrowsOnFailure(): void
    {
        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage(lang('Fabricator.createFailed', ['job', 'Too short, man!']));

        fake(ValidModel::class, ['name' => 'eh']);
    }

    public function testHelperDoesNotPersist(): void
    {
        helper('test');
        $result = fake(UserModel::class, ['name' => 'Derek'], false);
        $this->assertSame('Derek', $result->name);
        $this->dontSeeInDatabase('user', ['name' => 'Derek']);
    }
}
