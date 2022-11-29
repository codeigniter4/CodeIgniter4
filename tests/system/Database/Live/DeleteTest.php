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

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DeleteTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testDeleteThrowExceptionWithNoCriteria()
    {
        $this->expectException(DatabaseException::class);

        $this->db->table('job')->delete();
    }

    public function testDeleteWithExternalWhere()
    {
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $this->db->table('job')->where('name', 'Developer')->delete();

        $this->dontSeeInDatabase('job', ['name' => 'Developer']);
    }

    public function testDeleteWithInternalWhere()
    {
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $this->db->table('job')->delete(['name' => 'Developer']);

        $this->dontSeeInDatabase('job', ['name' => 'Developer']);
    }

    public function testDeleteWithLimit()
    {
        $this->seeNumRecords(2, 'user', ['country' => 'US']);

        try {
            $this->db->table('user')->delete(['country' => 'US'], 1);
        } catch (DatabaseException $e) {
            if (str_contains($e->getMessage(), 'does not allow LIMITs on DELETE queries.')) {
                return;
            }
        }

        $this->seeNumRecords(1, 'user', ['country' => 'US']);
    }

    public function testDeleteBatch()
    {
        $data = [
            ['userid' => 1, 'username' => 'Derek J', 'unused' => 'You can have fields you dont use'],
            ['userid' => 2, 'username' => 'Ahmadinejad', 'unused' => 'You can have fields you dont use'],
        ];

        $builder = $this->db->table('user')
            ->setData($data, null, 'data')
            ->onConstraint(['id' => 'userid', 'name' => 'username']);

        // SQLite does not support where for batch deletes
        if ($this->db->DBDriver !== 'SQLite3') {
            $builder->where('data.userid > 0');
        }

        $builder->deleteBatch();

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'name' => 'Derek Jones']);

        $this->dontSeeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'name' => 'Ahmadinejad']);
    }
}
