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
final class GetNumRowsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    /**
     * Added as instructed at https://codeigniter4.github.io/userguide/testing/database.html#the-test-class
     * {@inheritDoc}
     *
     * @see \CodeIgniter\Test\CIDatabaseTestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Added as instructed at https://codeigniter4.github.io/userguide/testing/database.html#the-test-class
     * {@inheritDoc}
     *
     * @see \CodeIgniter\Test\CIDatabaseTestCase::tearDown()
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * tests newly added ResultInterface::getNumRows with a live db
     */
    public function testGetRowNum(): void
    {
        $query = $this->db->table('job')->get();
        $this->assertSame(4, $query->getNumRows());
    }
}
