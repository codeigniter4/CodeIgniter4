<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\OCI8;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Exception;
use stdclass;
use Tests\Support\Database\Seeds\CITestSeeder;
use Throwable;

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

        if ($this->db->DBDriver !== 'OCI8') {
            $this->markTestSkipped('Only OCI8 has its own implementation.');
        }
    }

    public function testSimpleUpsertBatchTest()
    {
        // A rebate table - rebate reciept REBATEREC primary key
        // One invoic/line number can have multiple rebates applied but never the same one twice
        $sql = '
        BEGIN
        EXECUTE IMMEDIATE \'DROP TABLE "REBATE"\';
        EXCEPTION
        WHEN OTHERS THEN
        IF sqlcode != -0942 THEN RAISE; END IF;
        END;
        ';
        $this->db->query($sql);

        $sql = '
        CREATE TABLE "REBATE"
        ( "REBATEREC" NUMBER(10),
          "REBATE" NUMBER(10),
          "INVOICE" NUMBER(10),
          "LINE" NUMBER(10),
          "PRICE" NUMBER(12,2)
        )
        ';
        $this->db->query($sql);

        $sql = 'CREATE UNIQUE INDEX "REBATEREC" ON "REBATE" ("REBATEREC")';
        $this->db->query($sql);

        $sql = 'CREATE UNIQUE INDEX "REBATE" ON "REBATE" ("REBATE", "INVOICE", "LINE")';
        $this->db->query($sql);

        // some versions don't require this
        $sql = '
        BEGIN
        EXECUTE IMMEDIATE \'CREATE SEQUENCE REBATE_SEQ START WITH 1\';
        EXCEPTION
        WHEN OTHERS THEN
        IF sqlcode != -00955 THEN RAISE; END IF;
        END;
        ';

        try {
            $this->db->query($sql);
        } catch (Throwable $e) {
            $error = preg_replace('/begin case declare.*?json_array/s', ' ', var_export($this->db->query('select name, line, position, text from user_errors')->getResultObject(), true));

            throw new Exception($error);
        }

        $sql = '
        CREATE OR REPLACE NONEDITIONABLE TRIGGER "REBATE_TRG"
        BEFORE INSERT ON REBATE
        FOR EACH ROW
        BEGIN
        <<COLUMN_SEQUENCES>>
        BEGIN
        IF INSERTING AND :NEW.REBATEREC IS NULL THEN
        SELECT REBATE_SEQ.NEXTVAL INTO :NEW.REBATEREC FROM DUAL;
        END IF;
        END COLUMN_SEQUENCES;
        END;
        ';

        try {
            $this->db->query($sql);
        } catch (Throwable $e) {
            $error = preg_replace('/begin case declare.*?json_array/s', ' ', var_export($this->db->query('select name, line, position, text from user_errors')->getResultObject(), true));

            throw new Exception($error);
        }

        $sql = 'ALTER TRIGGER "REBATE_TRG" ENABLE';
        $this->db->query($sql);

        $sql = 'ALTER TABLE "REBATE" MODIFY ("REBATEREC" NOT NULL ENABLE, "REBATE" NOT NULL ENABLE, "INVOICE" NOT NULL ENABLE, "LINE" NOT NULL ENABLE)';
        $this->db->query($sql);

        $sql = 'ALTER TABLE "REBATE" ADD CONSTRAINT "REBATEREC" PRIMARY KEY ("REBATEREC")';
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

        try {
            $this->db->table('REBATE')->upsertBatch($data);
        } catch (Throwable $e) {
            $error = var_export($model->db->query("SELECT OWNER, OBJECT_NAME, STATUS FROM ALL_OBJECTS WHERE OBJECT_TYPE IN ('TABLE','table') AND OBJECT_NAME IN('rebate','REBATE','JOBS','jobs')")->getResultObject(), true); 
            $error .= var_export($model->db->query("SELECT sys_context('USERENV', 'CURRENT_SCHEMA') CSCHEMA, sys_context('USERENV', 'CURRENT_USER') CUSER FROM DUAL")->getResultObject(), true); 

            throw new Exception($error);
        }

        // see that the null record was created
        $results = $this->db->table('REBATE')->where('REBATE', 233)->where('INVOICE', 33453)->where('LINE', 2)->get()->getResultObject();
        $price   = $results[0]->PRICE;

        $this->assertSame($price, '66.66');

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

        $this->assertSame($price, '44.99');
    }
}
