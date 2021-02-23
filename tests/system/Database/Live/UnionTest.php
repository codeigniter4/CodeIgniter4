<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class UnionTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	//--------------------------------------------------------------------

	public function testUnion()
	{
		$rows = $this->db->table('user')
			->select('name')
			->where('country', 'US')
			->limit(1)
			->orderBy('name', 'DESC')
			->union(function (BaseBuilder $builder) {
				return $builder->select('name')
					->from('job')
					->where('id >', 1)
					->limit(2);
			})
			->orderBy('name')
			->get()
			->getResult();

		$this->assertEquals(3, count($rows));
		$this->assertEquals('Accountant', $rows[0]->name);
		$this->assertEquals('Politician', $rows[1]->name);
		$this->assertEquals('Richard A Causey', $rows[2]->name);
	}
}
