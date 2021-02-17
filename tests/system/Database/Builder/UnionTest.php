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
			->orderBy('title')
			->union(function (BaseBuilder $builder) {
				return $builder->select('title, year')->from('top_movies');
			});

		$sql = 'SELECT * FROM (SELECT "title", "year" FROM "movies" ORDER BY "title") as wrapper_alias '
			. 'UNION SELECT "title", "year" FROM "top_movies"';

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
			->orderBy('title')
			->unionAll(function (BaseBuilder $builder) {
				return $builder->select('title, year')
					->from('top_movies')
					->orderBy('title');
			});

		$sql = 'SELECT * FROM (SELECT "title", "year" FROM "movies" ORDER BY "title") as wrapper_alias '
			. 'UNION ALL SELECT * FROM (SELECT "title", "year" FROM "top_movies" ORDER BY "title") as wrapper_alias';

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

		$sql = 'SELECT "title", "year" FROM "movies" '
			. 'UNION ALL SELECT "title", "year" FROM "top_movies" ORDER BY "title" DESC, "year" ASC';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));

		$builder->resetQuery();

		$builder->select('title, year')
			->union(function (BaseBuilder $builder) {
				return $builder->select('title, year')
					->from('top_movies')
					->unionAll(function (BaseBuilder $builder) {
						return $builder->select('title, year')->from('tomato_movies');
					})->unionOrderBy('title', 'DESC');
			})->unionOrderBy('title');

		$sql = 'SELECT "title", "year" FROM "movies" '
			. 'UNION SELECT * FROM (SELECT "title", "year" FROM "top_movies" '
			. 'UNION ALL SELECT "title", "year" FROM "tomato_movies" ORDER BY "title" DESC) as wrapper_alias ORDER BY "title"';

		$this->assertEquals($sql, str_replace("\n", ' ', $builder->getCompiledSelect()));

		$builder->select('title, year')
			->union(function (BaseBuilder $builder) {
				return $builder->select('title, year')
					->from('top_movies')
					->unionAll(function (BaseBuilder $builder) {
						return $builder->select('title, year')->from('tomato_movies');
					})->limit(1);
			})
			->unionOrderBy('title');

		$sql = 'SELECT "title", "year" FROM "movies" '
			. 'UNION SELECT * FROM (SELECT "title", "year" FROM "top_movies"  LIMIT 1) as wrapper_alias '
			. 'UNION ALL SELECT "title", "year" FROM "tomato_movies" ORDER BY "title"';

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
