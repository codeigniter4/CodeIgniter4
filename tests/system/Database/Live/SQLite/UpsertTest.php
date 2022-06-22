<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\SQLite;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use stdclass;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class UpsertTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->db->DBDriver !== 'SQLite3') {
            $this->markTestSkipped('Only SQLite3 has its own implementation.');
        }
    }

    public function testSimpleUpsertBatchTest()
    {
        // A rebate table - rebate reciept REBATEREC primary key
        // One invoic/line number can have multiple rebates applied but never the same one twice
        $sql = '
        DROP TABLE IF EXISTS db_REBATE;
        CREATE TABLE db_REBATE (
        REBATEREC INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        REBATE INTEGER NOT NULL,
        INVOICE INTEGER NOT NULL,
        LINE INTEGER NOT NULL,
        PRICE DECIMAL(10,2),
        CONSTRAINT REBATE_IDX UNIQUE(REBATE,INVOICE,LINE))
        ';
        $this->db->query($sql);

        $data = [];

        // now upsertBatch
        $row            = new stdclass();
        $row->REBATEREC = 2;
        $row->REBATE    = 100;
        $row->INVOICE   = 12345;
        $row->LINE      = 1;
        $row->PRICE     = 22.99;
        $data[]         = $row;

        $row            = new stdclass();
        $row->REBATEREC = 3;
        $row->REBATE    = 100;
        $row->INVOICE   = 12345;
        $row->LINE      = 2;
        $row->PRICE     = 33.99;
        $data[]         = $row;

        $row            = new stdclass();
        $row->REBATEREC = 4;
        $row->REBATE    = 101;
        $row->INVOICE   = 12345;
        $row->LINE      = 1;
        $row->PRICE     = 44.99;
        $data[]         = $row;

        $row            = new stdclass();
        $row->REBATEREC = 7;
        $row->REBATE    = 233;
        $row->INVOICE   = 33453;
        $row->LINE      = 1;
        $row->PRICE     = 55.99;
        $data[]         = $row;

        $row            = new stdclass();
        $row->REBATEREC = null;
        $row->REBATE    = 233;
        $row->INVOICE   = 33453;
        $row->LINE      = 2;
        $row->PRICE     = 66.66;
        $data[]         = $row;

        $this->db->table('REBATE')->upsertBatch($data);

        // see that the null record was created
        $results = $this->db->table('REBATE')->where('REBATE', 233)->where('INVOICE', 33453)->where('LINE', 2)->get()->getResultObject();
        $price   = $results[0]->PRICE;

        $this->assertSame($price, 66.66);

        // see that we have inserted all records
        $results = $this->db->table('REBATE')->get()->getResultObject();

        $this->assertCount(5, $results);
    }

    public function testSimpleUpsertTest()
    {
        $row            = [];
        $row['REBATE']  = 101;
        $row['INVOICE'] = 12345;
        $row['LINE']    = 1;
        $row['PRICE']   = 44.99;

        $this->db->table('REBATE')->upsert($row);

        $results = $this->db->table('REBATE')->where('REBATE', 101)->where('INVOICE', 12345)->where('LINE', 1)->get()->getResultObject();
        $price   = $results[0]->PRICE;

        $this->assertSame($price, 44.99);
    }
}
