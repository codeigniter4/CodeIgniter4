<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class GetNumRowsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

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
    public function testGetRowNum()
    {
        $query = $this->db->table('job')->get();
        $this->assertSame(4, $query->getNumRows());
    }
}
