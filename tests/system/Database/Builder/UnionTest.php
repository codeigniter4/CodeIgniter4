<?php namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\Mock\MockConnection;

class UnionTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	public function testUnion()
	{
		$builder = $this->db->table('movies');

		$builder->select('title, year')
			->union(function (BaseBuilder $builder) {
				return $builder->select('title, year')->from('top_movies');
			});

		$sql = '(SELECT "title", "year" FROM "movies") UNION (SELECT "title", "year" FROM "top_movies")';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	public function testUnionExceptions()
	{
		$builder = $this->db->table('movies');
		$builder->select('title, year');

		$this->expectException(\TypeError::class);

		$builder->union(1);

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage(
			'BaseBuilder::union(). The closure must return an instance of the BaseBuilder class'
		);

		$builder->union(function (BaseBuilder $builder) {
			$builder->select('title, year')->from('top_movies');
		});
	}

	public function testUnionAll()
	{
		$builder = $this->db->table('movies');

		$builder->select('title, year')
			->unionAll(function (BaseBuilder $builder) {
				return $builder->select('title, year')->from('top_movies');
			});

		$sql = '(SELECT "title", "year" FROM "movies") UNION ALL (SELECT "title", "year" FROM "top_movies")';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	public function testMultiQueryUnion()
	{
		$builder = $this->db->table('movies');

		$builder->select('title, year')
			->union(function (BaseBuilder $builder) {
				return $builder->select('title, year')
					->from('top_movies')
					->unionAll(function (BaseBuilder $builder) {
						return $builder->select('title, year')->from('tomato_movies');
					});
			});

		$sql = '(SELECT "title", "year" FROM "movies") '
				. 'UNION ((SELECT "title", "year" FROM "top_movies") '
					. 'UNION ALL (SELECT "title", "year" FROM "tomato_movies"))';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));

		$builder->resetQuery();

		$builder->select('title, year')
			->union(function (BaseBuilder $builder) {
				return $builder->select('title, year')->from('top_movies');
			})->unionAll(function (BaseBuilder $builder) {
				return $builder->select('title, year')->from('tomato_movies');
			});

		$sql = '(SELECT "title", "year" FROM "movies") '
				. 'UNION (SELECT "title", "year" FROM "top_movies") '
				. 'UNION ALL (SELECT "title", "year" FROM "tomato_movies")';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));

		$builder->resetQuery();

		$year1 = 2000;
		$year2 = 2010;
		$year3 = 2020;

		$builder->select('title, year')
			->where('year', $year1)
			->union(function (BaseBuilder $builder) use ($year2, $year3) {
				return $builder->select('title, year')
					->from('top_movies')
					->where('year', $year2)
					->unionAll(function (BaseBuilder $builder) use ($year3) {
						return $builder->select('title, year')
							->from('tomato_movies')
							->where('year', $year3);
					});
			});

		$sql = '(SELECT "title", "year" FROM "movies" WHERE "year" = 2000) '
			. 'UNION ((SELECT "title", "year" FROM "top_movies" WHERE "year" = 2010) '
			. 'UNION ALL (SELECT "title", "year" FROM "tomato_movies" WHERE "year" = 2020))';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	public function testUnionOrderBy()
	{
		$builder = $this->db->table('movies');

		$builder->select('title, year')
			->unionAll(function (BaseBuilder $builder) {
				return $builder->select('title, year')->from('top_movies');
			})
			->unionOrderBy('title', 'DESC')
			->unionOrderBy('year', 'ASC');

		$sql = '(SELECT "title", "year" FROM "movies") '
			. 'UNION ALL (SELECT "title", "year" FROM "top_movies") ORDER BY "title" DESC, "year" ASC';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));

		$builder->resetQuery();

		$builder->select('title, year')
			->union(function (BaseBuilder $builder) {
				return $builder->select('title, year')
					->from('top_movies')
					->unionAll(function (BaseBuilder $builder) {
						return $builder->select('title, year')->from('tomato_movies');
					})->unionOrderBy('title', 'DESC');
			});

		$sql = '(SELECT "title", "year" FROM "movies") '
			. 'UNION ((SELECT "title", "year" FROM "top_movies") '
			. 'UNION ALL (SELECT "title", "year" FROM "tomato_movies") ORDER BY "title" DESC)';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	public function testIgnoreUnionOrderByWithoutUnionQuery()
	{
		$builder = $this->db->table('movies');

		$builder->select('title, year')->unionOrderBy('year', 'ASC');

		$sql = 'SELECT "title", "year" FROM "movies"';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}
}
