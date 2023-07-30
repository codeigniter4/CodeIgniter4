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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DatabaseTestTraitCaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testHasInDatabase(): void
    {
        $this->hasInDatabase('user', ['name' => 'Ricky', 'email' => 'sofine@example.com', 'country' => 'US']);

        $this->seeInDatabase('user', ['name' => 'Ricky', 'email' => 'sofine@example.com', 'country' => 'US']);
    }

    public function testDontSeeInDatabase(): void
    {
        $this->dontSeeInDatabase('user', ['name' => 'Ricardo']);
    }

    public function testSeeNumRecords(): void
    {
        $this->seeNumRecords(2, 'user', ['country' => 'US']);
    }

    public function testGrabFromDatabase(): void
    {
        $email = $this->grabFromDatabase('user', 'email', ['name' => 'Derek Jones']);

        $this->assertSame('derek@world.com', $email);
    }

    public function testSeeInDatabase(): void
    {
        $this->hasInDatabase('user', [
            'name'    => 'Ricardo',
            'email'   => 'ricardo@example.com',
            'country' => 'The Moon',
        ]);

        $this->seeInDatabase('user', ['name' => 'Ricardo']);
    }
}
