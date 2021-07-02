<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class CountTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testCountAll()
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs"';

        $this->assertSame($expectedSQL, $builder->countAll(true));
    }

    public function testCountAllResults()
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->countAllResults(false);

        $expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs" WHERE "id" > :id:';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    public function testCountAllResultsWithGroupBy()
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->groupBy('id');
        $builder->testMode();

        $answer = $builder->where('id >', 3)->countAllResults(false);

        $expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM ( SELECT * FROM "jobs" WHERE "id" > :id: GROUP BY "id" ) CI_count_all_results';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3651
     */
    public function testCountAllResultsWithGroupByAndPrefix()
    {
        $this->db = new MockConnection(['DBPrefix' => 'ci_']);

        $builder = new BaseBuilder('jobs', $this->db);
        $builder->select('jobs.*')->where('id >', 3)->groupBy('id')->testMode();

        $expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM ( SELECT "ci_jobs".* FROM "ci_jobs" WHERE "id" > :id: GROUP BY "id" ) CI_count_all_results';

        $answer1 = $builder->countAllResults(false);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer1));

        // We run the query one more time to make sure the DBPrefix is added only once
        $answer2 = $builder->countAllResults(false);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer2));
    }

    public function testCountAllResultsWithGroupByAndHaving()
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->groupBy('id');
        $builder->having('1=1');
        $builder->testMode();

        $answer = $builder->where('id >', 3)->countAllResults(false);

        $expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM ( SELECT * FROM "jobs" WHERE "id" > :id: GROUP BY "id" HAVING 1 = 1 ) CI_count_all_results';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    public function testCountAllResultsWithHavingOnly()
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->having('1=1');
        $builder->testMode();

        $answer = $builder->where('id >', 3)->countAllResults(false);

        $expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs" WHERE "id" > :id: HAVING 1 = 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }
}
