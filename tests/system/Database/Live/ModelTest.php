<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Model;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\UserModel;

/**
 * @group DatabaseLive
 */
class ModelTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function setUp()
	{
	    parent::setUp();

		$this->model = new Model($this->db);
	}

	//--------------------------------------------------------------------

	public function testHashIDsWithNumber()
	{
	    $expected = '123';

		$str = $this->model->encodeID($expected);

		$this->assertNotEquals($expected, $str);

		$this->assertEquals($expected, $this->model->decodeID($str));
	}

	//--------------------------------------------------------------------

	public function testHashIDsWithString()
	{
		$expected = 'my test hash';

		$str = $this->model->encodeID($expected);

		$this->assertNotEquals($expected, $str);

		$this->assertEquals($expected, $this->model->decodeID($str));
	}

	//--------------------------------------------------------------------

	public function testHashedIdsWithFind()
	{
		$hash = $this->model->encodeId(4);

		$this->model->setTable('job')
					->withDeleted();

		$user = $this->model->asObject()
							->findByHashedID($hash);

		$this->assertNotEmpty($user);
		$this->assertEquals(4, $user->id);
	}

	//--------------------------------------------------------------------

	public function testFindReturnsRow()
	{
	    $model = new JobModel($this->db);

		$job = $model->find(4);

		$this->assertEquals('Musician', $job->name);
	}

	//--------------------------------------------------------------------

	public function testFindReturnsMultipleRows()
	{
		$model = new JobModel($this->db);

		$job = $model->find([1,4]);

		$this->assertEquals('Developer', $job[0]->name);
		$this->assertEquals('Musician',  $job[1]->name);
	}

	//--------------------------------------------------------------------

	public function testFindRespectsReturnArray()
	{
		$model = new JobModel($this->db);

		$job = $model->asArray()->find(4);

		$this->assertTrue(is_array($job));
	}

	//--------------------------------------------------------------------

	public function testFindRespectsReturnObject()
	{
		$model = new JobModel($this->db);

		$job = $model->asObject()->find(4);

		$this->assertTrue(is_object($job));
	}

	//--------------------------------------------------------------------

	public function testFindRespectsSoftDeletes()
	{
		$this->db->table('user')->where('id', 4)->update(['deleted' => 1]);

		$model = new UserModel($this->db);

		$user = $model->asObject()->find(4);

		$this->assertTrue(empty($user));

		$user = $model->withDeleted()->find(4);

		$this->assertEquals(1, count($user));
	}

	//--------------------------------------------------------------------

	public function testFindWhereSimple()
	{
	    $model = new JobModel($this->db);

		$jobs = $model->asObject()->findWhere('id >', 2);

		$this->assertEquals(2, count($jobs));
		$this->assertEquals('Accountant', $jobs[0]->name);
		$this->assertEquals('Musician',   $jobs[1]->name);
	}

	//--------------------------------------------------------------------

	public function testFindWhereWithArrayWhere()
	{
		$model = new JobModel($this->db);

		$jobs = $model->asArray()->findWhere(['id' => 1]);

		$this->assertEquals(1, count($jobs));
		$this->assertEquals('Developer', $jobs[0]['name']);
	}

	//--------------------------------------------------------------------

	public function testFindWhereRespectsSoftDeletes()
	{
		$this->db->table('user')->where('id', 4)->update(['deleted' => 1]);

		$model = new UserModel($this->db);

		$user = $model->findWhere('id >', '2');

		$this->assertEquals(1, count($user));

		$user = $model->withDeleted()->findWhere('id >', 2);

		$this->assertEquals(2, count($user));
	}

	//--------------------------------------------------------------------

	public function testFindAllReturnsAllRecords()
	{
	    $model = new UserModel($this->db);

		$users = $model->findAll();

		$this->assertEquals(4, count($users));
	}

	//--------------------------------------------------------------------

	public function testFindAllRespectsLimits()
	{
		$model = new UserModel($this->db);

		$users = $model->findAll(2);

		$this->assertEquals(2, count($users));
		$this->assertEquals('Derek Jones', $users[0]->name);
	}

	//--------------------------------------------------------------------

	public function testFindAllRespectsLimitsAndOffset()
	{
		$model = new UserModel($this->db);

		$users = $model->findAll(2, 2);

		$this->assertEquals(2, count($users));
		$this->assertEquals('Richard A Causey', $users[0]->name);
	}

	//--------------------------------------------------------------------

	public function testFindAllRespectsSoftDeletes()
	{
		$this->db->table('user')->where('id', 4)->update(['deleted' => 1]);

		$model = new UserModel($this->db);

		$user = $model->findAll();

		$this->assertEquals(3, count($user));

		$user = $model->withDeleted()->findAll();

		$this->assertEquals(4, count($user));
	}

	//--------------------------------------------------------------------

	public function testFirst()
	{
	    $model = new UserModel();

		$user = $model->where('id >', 2)->first();

		$this->assertEquals(1, count($user));
		$this->assertEquals(3, $user->id);
	}

	//--------------------------------------------------------------------

	public function testFirstRespectsSoftDeletes()
	{
		$this->db->table('user')->where('id', 1)->update(['deleted' => 1]);

		$model = new UserModel();

		$user = $model->first();

		$this->assertEquals(1, count($user));
		$this->assertEquals(2, $user->id);

		$user = $model->withDeleted()->first();

		$this->assertEquals(1, $user->id);
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 */
	public function testSaveNewRecordObject()
	{
	    $model = new JobModel();

		$data = new \stdClass();
		$data->name = 'Magician';
		$data->description = 'Makes peoples things dissappear.';
		
		$model->protect(false)->save($data);

		$this->seeInDatabase('job', ['name' => 'Magician']);
	}

	//--------------------------------------------------------------------

	public function testSaveNewRecordArray()
	{
		$model = new JobModel();

		$data = [
			'name' => 'Apprentice',
		    'description' => 'That thing you do.'
		];

		$result = $model->protect(false)->save($data);

		$this->seeInDatabase('job', ['name' => 'Apprentice']);
	}

	//--------------------------------------------------------------------

	public function testSaveUpdateRecordObject()
	{
		$model = new JobModel();

		$data = [
			'id' => 1,
			'name' => 'Apprentice',
			'description' => 'That thing you do.'
		];

		$result = $model->protect(false)->save($data);

		$this->seeInDatabase('job', ['name' => 'Apprentice']);
		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testSaveUpdateRecordArray()
	{
		$model = new JobModel();

		$data = new \stdClass();
		$data->id = 1;
		$data->name = 'Engineer';
		$data->description = 'A fancier term for Developer.';

		$result = $model->protect(false)->save($data);

		$this->seeInDatabase('job', ['name' => 'Engineer']);
		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testSaveProtected()
	{
		$model = new JobModel();

		$data = new \stdClass();
		$data->id = 1;
		$data->name = 'Engineer';
		$data->description = 'A fancier term for Developer.';

		$this->setExpectedException('CodeIgniter\DatabaseException');

		$model->protect(true)->save($data);
	}

	//--------------------------------------------------------------------

	public function testDeleteBasics()
	{
	    $model = new JobModel();

		$this->seeInDatabase('job', ['name' =>'Developer']);

		$model->delete(1);

		$this->dontSeeInDatabase('job', ['name' => 'Developer']);
	}

	//--------------------------------------------------------------------

	public function testDeleteWithSoftDeletes()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' =>'Derek Jones', 'deleted' => 0]);

		$model->delete(1);

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted' => 1]);
	}

	//--------------------------------------------------------------------

	public function testDeleteWithSoftDeletesPurge()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' =>'Derek Jones', 'deleted' => 0]);

		$model->delete(1, true);

		$this->dontSeeInDatabase('user', ['name' => 'Derek Jones']);
	}

	//--------------------------------------------------------------------

	public function testDeleteWhereWithSoftDeletes()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' =>'Derek Jones', 'deleted' => 0]);

		$model->deleteWhere('name', 'Derek Jones');

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted' => 1]);
	}

	//--------------------------------------------------------------------

	public function testDeleteWhereWithSoftDeletesPurge()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' =>'Derek Jones', 'deleted' => 0]);

		$model->deleteWhere('name', 'Derek Jones', true);

		$this->dontSeeInDatabase('user', ['name' => 'Derek Jones']);
	}

	//--------------------------------------------------------------------

	public function testPurgeDeleted()
	{
	    $model = new UserModel();

		$this->db->table('user')->where('id', 1)->update(['deleted' => 1]);

		$model->purgeDeleted();

		$users = $model->withDeleted()->findAll();

		$this->assertEquals(3, count($users));
	}

	//--------------------------------------------------------------------

	public function testOnlyDeleted()
	{
		$model = new UserModel($this->db);

		$this->db->table('user')->where('id', 1)->update(['deleted' => 1]);

		$users = $model->onlyDeleted()->findAll();

		$this->assertEquals(1, count($users));
	}

	//--------------------------------------------------------------------

	public function testChunk()
	{
	    $model = new UserModel();

		$rowCount = 0;

		$model->chunk(2, function($row) use (&$rowCount) {
			$rowCount++;
		});

		$this->assertEquals(4, $rowCount);
	}

	//--------------------------------------------------------------------


}