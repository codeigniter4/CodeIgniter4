<?php

namespace CodeIgniter\Database\Live;

use BadMethodCallException;
use CodeIgniter\Config\Config;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Config\Services;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SecondaryModel;
use Tests\Support\Models\SimpleEntity;
use Tests\Support\Models\StringifyPkeyModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidErrorsModel;
use Tests\Support\Models\ValidModel;
use Tests\Support\Models\WithoutAutoincrementModel;

/**
 * @group DatabaseLive
 */
class ModelTest extends CIDatabaseTestCase
{
	use ReflectionHelper;

	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	protected function setUp(): void
	{
		parent::setUp();

		$this->model = new Model($this->db);
	}

	//--------------------------------------------------------------------

	public function tearDown(): void
	{
		parent::tearDown();

		Services::reset();
		Config::reset();
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

		$job = $model->find([1, 4]);

		$this->assertEquals('Developer', $job[0]->name);
		$this->assertEquals('Musician', $job[1]->name);
	}

	//--------------------------------------------------------------------

	public function testGetColumnWithStringColumnName()
	{
		$model = new JobModel($this->db);

		$job = $model->findColumn('name');

		$this->assertEquals('Developer', $job[0]);
		$this->assertEquals('Politician', $job[1]);
		$this->assertEquals('Accountant', $job[2]);
		$this->assertEquals('Musician', $job[3]);
	}

	//--------------------------------------------------------------------

	public function testGetColumnsWithMultipleColumnNames()
	{
		$model = new JobModel($this->db);

		$this->expectException(DataException::class);
		$this->expectExceptionMessage('Only single column allowed in Column name.');

		$job = $model->findColumn('name,description');
	}

	//--------------------------------------------------------------------

	public function testFindActsAsGetWithNoParams()
	{
		$model = new JobModel($this->db);

		$jobs = $model->asArray()
					  ->find();

		$this->assertCount(4, $jobs);

		$names = array_column($jobs, 'name');
		$this->assertTrue(in_array('Developer', $names));
		$this->assertTrue(in_array('Politician', $names));
		$this->assertTrue(in_array('Accountant', $names));
		$this->assertTrue(in_array('Musician', $names));
	}

	//--------------------------------------------------------------------

	public function testFindRespectsReturnArray()
	{
		$model = new JobModel($this->db);

		$job = $model->asArray()
					 ->find(4);

		$this->assertIsArray($job);
	}

	//--------------------------------------------------------------------

	public function testFindRespectsReturnObject()
	{
		$model = new JobModel($this->db);

		$job = $model->asObject()
					 ->find(4);

		$this->assertIsObject($job);
	}

	//--------------------------------------------------------------------

	public function testFindRespectsSoftDeletes()
	{
		$this->db->table('user')
				 ->where('id', 4)
				 ->update(['deleted_at' => date('Y-m-d H:i:s')]);

		$model = new UserModel($this->db);

		$user = $model->asObject()
					  ->find(4);

		$this->assertEmpty($user);

		$user = $model->withDeleted()
					  ->find(4);

		// fix for PHP7.2
		$count = is_array($user) ? count($user) : 1;
		$this->assertEquals(1, $count);
	}

	//--------------------------------------------------------------------

	public function testFindClearsBinds()
	{
		$model = new JobModel($this->db);

		$model->find(1);
		$model->find(1);

		// Binds should be reset to 0 after each one
		$binds = $model->builder()
					   ->getBinds();
		$this->assertCount(0, $binds);

		$query = $model->getLastQuery();
		$this->assertCount(1, $this->getPrivateProperty($query, 'binds'));
	}

	//--------------------------------------------------------------------

	public function testFindAllReturnsAllRecords()
	{
		$model = new UserModel($this->db);

		$users = $model->findAll();

		$this->assertCount(4, $users);
	}

	//--------------------------------------------------------------------

	public function testFindAllRespectsLimits()
	{
		$model = new UserModel($this->db);

		$users = $model->findAll(2);

		$this->assertCount(2, $users);
		$this->assertEquals('Derek Jones', $users[0]->name);
	}

	//--------------------------------------------------------------------

	public function testFindAllRespectsLimitsAndOffset()
	{
		$model = new UserModel($this->db);

		$users = $model->findAll(2, 2);

		$this->assertCount(2, $users);
		$this->assertEquals('Richard A Causey', $users[0]->name);
	}

	//--------------------------------------------------------------------

	public function testFindAllRespectsSoftDeletes()
	{
		$this->db->table('user')
				 ->where('id', 4)
				 ->update(['deleted_at' => date('Y-m-d H:i:s')]);

		$model = new UserModel($this->db);

		$user = $model->findAll();

		$this->assertCount(3, $user);

		$user = $model->withDeleted()
					  ->findAll();

		$this->assertCount(4, $user);
	}

	//--------------------------------------------------------------------

	public function testFirst()
	{
		$model = new UserModel();

		$user = $model->where('id >', 2)
					  ->first();

		// fix for PHP7.2
		$count = is_array($user) ? count($user) : 1;
		$this->assertEquals(1, $count);
		$this->assertEquals(3, $user->id);
	}

	//--------------------------------------------------------------------

	public function provideGroupBy()
	{
		return [
			[
				true,
				3,
			],
			[
				false,
				7,
			],
		];
	}

	/**
	 * @dataProvider provideGroupBy
	 */
	public function testFirstAggregate($groupBy, $total)
	{
		$model = new UserModel();

		if ($groupBy)
		{
			$model->groupBy('id');
		}

		$user = $model->select('SUM(id) as total')
					  ->where('id >', 2)
					  ->first();

		$this->assertEquals($total, $user->total);
	}

	//--------------------------------------------------------------------

	public function provideAggregateAndGroupBy()
	{
		return [
			[
				true,
				true,
			],
			[
				false,
				false,
			],
			[
				true,
				false,
			],
			[
				false,
				true,
			],
		];
	}

	/**
	 * @dataProvider provideAggregateAndGroupBy
	 */
	public function testFirstRespectsSoftDeletes($aggregate, $groupBy)
	{
		$this->db->table('user')
				 ->where('id', 1)
				 ->update(['deleted_at' => date('Y-m-d H:i:s')]);

		$model = new UserModel();
		if ($aggregate)
		{
			$model->select('SUM(id) as id');
		}

		if ($groupBy)
		{
			$model->groupBy('id');
		}

		$user = $model->first();

		if (! $aggregate || $groupBy)
		{
			// fix for PHP7.2
			$count = is_array($user) ? count($user) : 1;
			$this->assertEquals(1, $count);
			$this->assertEquals(2, $user->id);
		}
		else
		{
			$this->assertEquals(9, $user->id);
		}

		$user = $model->withDeleted()
					  ->first();

		$this->assertEquals(1, $user->id);
	}

	//--------------------------------------------------------------------

	public function testFirstWithNoPrimaryKey()
	{
		$model = new SecondaryModel();

		$this->db->table('secondary')
				 ->insert([
					 'id'    => 1,
					 'key'   => 'foo',
					 'value' => 'bar',
				 ]);
		$this->db->table('secondary')
				 ->insert([
					 'id'    => 2,
					 'key'   => 'bar',
					 'value' => 'baz',
				 ]);

		$record = $model->first();

		$this->assertInstanceOf('stdClass', $record);
		$this->assertEquals('foo', $record->key);
	}

	//--------------------------------------------------------------------

	public function testSaveNewRecordObject()
	{
		$model = new JobModel();

		$data              = new \stdClass();
		$data->name        = 'Magician';
		$data->description = 'Makes peoples things dissappear.';

		$model->protect(false)
			  ->save($data);

		$this->seeInDatabase('job', ['name' => 'Magician']);
	}

	//--------------------------------------------------------------------

	public function testSaveNewRecordArray()
	{
		$model = new JobModel();

		$data = [
			'name'        => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$model->protect(false)
			  ->save($data);

		$this->seeInDatabase('job', ['name' => 'Apprentice']);
	}

	//--------------------------------------------------------------------

	public function testSaveNewRecordArrayFail()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', false);

		$model = new JobModel();

		$data = [
			'name123'     => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$result = $model->protect(false)
			  ->save($data);

		$this->assertFalse($result);

		$this->dontSeeInDatabase('job', ['name' => 'Apprentice']);
	}

	//--------------------------------------------------------------------

	public function testSaveUpdateRecordArray()
	{
		$model = new JobModel();

		$data = [
			'id'          => 1,
			'name'        => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$result = $model->protect(false)
						->save($data);

		$this->seeInDatabase('job', ['name' => 'Apprentice']);
		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testSaveUpdateRecordArrayFail()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', false);

		$model = new JobModel();

		$data = [
			'id'          => 1,
			'name123'     => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$result = $model->protect(false)
						->save($data);

		$this->assertFalse($result);

		$this->dontSeeInDatabase('job', ['name' => 'Apprentice']);
	}

	//--------------------------------------------------------------------

	public function testSaveUpdateRecordObject()
	{
		$model = new JobModel();

		$data              = new \stdClass();
		$data->id          = 1;
		$data->name        = 'Engineer';
		$data->description = 'A fancier term for Developer.';

		$result = $model->protect(false)
						->save($data);

		$this->seeInDatabase('job', ['name' => 'Engineer']);
		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testSaveProtected()
	{
		$model = new JobModel();

		$data               = new \stdClass();
		$data->id           = 1;
		$data->name         = 'Engineer';
		$data->description  = 'A fancier term for Developer.';
		$data->random_thing = 'Something wicked'; // If not protected, this would kill the script.

		$result = $model->protect(true)
						->save($data);

		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testDeleteBasics()
	{
		$model = new JobModel();

		$this->seeInDatabase('job', ['name' => 'Developer']);

		$result = $model->delete(1);
		$this->assertTrue($result->resultID !== false);

		$this->dontSeeInDatabase('job', ['name' => 'Developer']);
	}

	//--------------------------------------------------------------------

	public function testDeleteFail()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', false);

		$model = new JobModel();

		$this->seeInDatabase('job', ['name' => 'Developer']);

		$result = $model->where('name123', 'Developer')->delete();
		$this->assertFalse($result->resultID);

		$this->seeInDatabase('job', ['name' => 'Developer']);
	}

	//--------------------------------------------------------------------

	public function testDeleteStringPrimaryKey()
	{
		$model = new StringifyPkeyModel();

		$this->seeInDatabase('stringifypkey', ['value' => 'test']);

		$model->delete('A01');

		$this->dontSeeInDatabase('stringifypkey', ['value' => 'test']);
	}

	//--------------------------------------------------------------------

	public function testDeleteWithSoftDeletes()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

		$result = $model->delete(1);
		$this->assertTrue($result);

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NOT NULL' => null]);
	}

	//--------------------------------------------------------------------

	public function testDeleteWithSoftDeleteFail()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', false);

		$model = new UserModel();

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

		$result = $model->where('name123', 'Derek Jones')->delete();
		$this->assertFalse($result);

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
	}

	//--------------------------------------------------------------------

	public function testDeleteWithSoftDeletesPurge()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

		$model->delete(1, true);

		$this->dontSeeInDatabase('user', ['name' => 'Derek Jones']);
	}

	//--------------------------------------------------------------------

	public function testDeleteMultiple()
	{
		$model = new JobModel();

		$this->seeInDatabase('job', ['name' => 'Developer']);
		$this->seeInDatabase('job', ['name' => 'Politician']);

		$model->delete([1, 2]);

		$this->dontSeeInDatabase('job', ['name' => 'Developer']);
		$this->dontSeeInDatabase('job', ['name' => 'Politician']);
		$this->seeInDatabase('job', ['name' => 'Accountant']);
	}

	//--------------------------------------------------------------------

	public function testDeleteNoParams()
	{
		$model = new JobModel();

		$this->seeInDatabase('job', ['name' => 'Developer']);

		$model->where('id', 1)
			  ->delete();

		$this->dontSeeInDatabase('job', ['name' => 'Developer']);
	}

	//--------------------------------------------------------------------

	public function testPurgeDeleted()
	{
		$model = new UserModel();

		$this->db->table('user')
				 ->where('id', 1)
				 ->update(['deleted_at' => date('Y-m-d H:i:s')]);

		$model->purgeDeleted();

		$users = $model->withDeleted()
					   ->findAll();

		$this->assertCount(3, $users);
	}

	//--------------------------------------------------------------------

	public function testOnlyDeleted()
	{
		$model = new UserModel($this->db);

		$this->db->table('user')
				 ->where('id', 1)
				 ->update(['deleted_at' => date('Y-m-d H:i:s')]);

		$users = $model->onlyDeleted()
					   ->findAll();

		$this->assertCount(1, $users);
	}
	/**
	 * If where condition is set, beyond the value was empty (0,'', NULL, etc.),
	 * Exception should not be thrown because condition was explicity set
	 *
	 * @dataProvider emptyPkValues
	 * @return       void
	 */
	public function testDontThrowExceptionWhenSoftDeleteConditionIsSetWithEmptyValue($emptyValue)
	{
		$model = new UserModel();
		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
		$model->where('id', $emptyValue)->delete();
		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
		unset($model);
	}    //--------------------------------------------------------------------

	/**
	 * @dataProvider emptyPkValues
	 * @return       void
	 */
	public function testThrowExceptionWhenSoftDeleteParamIsEmptyValue($emptyValue)
	{
		$this->expectException('CodeIgniter\Database\Exceptions\DatabaseException');
		$this->expectExceptionMessage('Deletes are not allowed unless they contain a "where" or "like" clause.');

		$model = new UserModel();
		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
		$model->delete($emptyValue);
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider emptyPkValues
	 * @return       void
	 */
	public function testDontDeleteRowsWhenSoftDeleteParamIsEmpty($emptyValue)
	{
		$this->expectException('CodeIgniter\Database\Exceptions\DatabaseException');
		$this->expectExceptionMessage('Deletes are not allowed unless they contain a "where" or "like" clause.');

		$model = new UserModel();
		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
		$model->delete($emptyValue);
		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
		unset($model);
	}

	public function emptyPkValues()
	{
		return [
			[0],
			[null],
			['0'],
		];
	}
	//--------------------------------------------------------------------

	public function testChunk()
	{
		$model = new UserModel();

		$rowCount = 0;

		$model->chunk(2, function ($row) use (&$rowCount) {
			$rowCount++;
		});

		$this->assertEquals(4, $rowCount);
	}

	//--------------------------------------------------------------------

	public function testValidationBasics()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'        => null,
			'description' => 'some great marketing stuff',
		];

		$this->assertFalse($model->insert($data));

		$errors = $model->errors();

		$this->assertEquals('You forgot to name the baby.', $errors['name']);
	}

	//--------------------------------------------------------------------

	public function testValidationWithSetValidationRule()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'        => 'some name',
			'description' => 'some great marketing stuff',
		];

		$model->setValidationRule('description', [
			'rules'  => 'required|min_length[50]',
			'errors' => [
				'min_length' => 'Description is too short baby.',
			],
		]);
		$this->assertFalse($model->insert($data));

		$errors = $model->errors();

		$this->assertEquals('Description is too short baby.', $errors['description']);
	}

	//--------------------------------------------------------------------

	public function testValidationWithSetValidationRules()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'        => '',
			'description' => 'some great marketing stuff',
		];

		$model->setValidationRules([
			'name'        => [
				'rules'  => 'required',
				'errors' => [
					'required' => 'Give me a name baby.',
				],
			],
			'description' => [
				'rules'  => 'required|min_length[50]',
				'errors' => [
					'min_length' => 'Description is too short baby.',
				],
			],
		]);
		$this->assertFalse($model->insert($data));

		$errors = $model->errors();

		$this->assertEquals('Give me a name baby.', $errors['name']);
		$this->assertEquals('Description is too short baby.', $errors['description']);
	}

	//--------------------------------------------------------------------

	public function testValidationWithSetValidationMessage()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'        => null,
			'description' => 'some great marketing stuff',
		];

		$model->setValidationMessage('name', [
			'required'   => 'Your baby name is missing.',
			'min_length' => 'Too short, man!',
		]);
		$this->assertFalse($model->insert($data));

		$errors = $model->errors();

		$this->assertEquals('Your baby name is missing.', $errors['name']);
	}

	//--------------------------------------------------------------------

	public function testValidationWithSetValidationMessages()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'        => null,
			'description' => 'some great marketing stuff',
		];

		$model->setValidationMessages([
			'name' => [
				'required'   => 'Your baby name is missing.',
				'min_length' => 'Too short, man!',
			],
		]);

		$this->assertFalse($model->insert($data));

		$errors = $model->errors();

		$this->assertEquals('Your baby name is missing.', $errors['name']);
	}
	//--------------------------------------------------------------------

	public function testValidationPlaceholdersSuccess()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'  => 'abc',
			'id'    => 13,
			'token' => 13,
		];

		$this->assertTrue($model->validate($data));
	}

	//--------------------------------------------------------------------

	public function testValidationPlaceholdersFail()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'  => 'abc',
			'id'    => 13,
			'token' => 12,
		];

		$this->assertFalse($model->validate($data));
	}

	//--------------------------------------------------------------------

	public function testSkipValidation()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'        => '2',
			'description' => 'some great marketing stuff',
		];

		$this->assertIsNumeric($model->skipValidation(true)->insert($data));
	}

	//--------------------------------------------------------------------

	public function testCleanValidationRemovesAllWhenNoDataProvided()
	{
		$model   = new Model($this->db);
		$cleaner = $this->getPrivateMethodInvoker($model, 'cleanValidationRules');

		$rules = [
			'name' => 'required',
			'foo'  => 'bar',
		];

		$rules = $cleaner($rules, null);

		$this->assertEmpty($rules);
	}

	//--------------------------------------------------------------------

	public function testCleanValidationRemovesOnlyForFieldsNotProvided()
	{
		$model   = new Model($this->db);
		$cleaner = $this->getPrivateMethodInvoker($model, 'cleanValidationRules');

		$rules = [
			'name' => 'required',
			'foo'  => 'required',
		];

		$data = [
			'foo' => 'bar',
		];

		$rules = $cleaner($rules, $data);

		$this->assertTrue(array_key_exists('foo', $rules));
		$this->assertFalse(array_key_exists('name', $rules));
	}

	//--------------------------------------------------------------------

	public function testCleanValidationReturnsAllWhenAllExist()
	{
		$model   = new Model($this->db);
		$cleaner = $this->getPrivateMethodInvoker($model, 'cleanValidationRules');

		$rules = [
			'name' => 'required',
			'foo'  => 'required',
		];

		$data = [
			'foo'  => 'bar',
			'name' => null,
		];

		$rules = $cleaner($rules, $data);

		$this->assertTrue(array_key_exists('foo', $rules));
		$this->assertTrue(array_key_exists('name', $rules));
	}

	//--------------------------------------------------------------------

	public function testValidationPassesWithMissingFields()
	{
		$model = new ValidModel();

		$data = [
			'foo' => 'bar',
		];

		$result = $model->validate($data);

		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	public function testValidationWithGroupName()
	{
		$config            = new \Config\Validation();
		$config->grouptest = [
			'name'  => [
				'required',
				'min_length[3]',
			],
			'token' => 'in_list[{id}]',
		];

		$data = [
			'name'  => 'abc',
			'id'    => 13,
			'token' => 13,
		];

		\CodeIgniter\Config\Config::injectMock('Validation', $config);

		$model = new ValidModel($this->db);
		$this->setPrivateProperty($model, 'validationRules', 'grouptest');

		$this->assertTrue($model->validate($data));
	}

	//--------------------------------------------------------------------

	public function testCanCreateAndSaveEntityClasses()
	{
		$model = new EntityModel($this->db);

		$entity = $model->where('name', 'Developer')
						->first();

		$this->assertInstanceOf(SimpleEntity::class, $entity);
		$this->assertEquals('Developer', $entity->name);
		$this->assertEquals('Awesome job, but sometimes makes you bored', $entity->description);

		$time = time();

		$entity->name       = 'Senior Developer';
		$entity->created_at = $time;

		$this->assertTrue($model->save($entity));

		$result = $model->where('name', 'Senior Developer')
						->get()
						->getFirstRow();

		$this->assertEquals(date('Y-m-d', $time), date('Y-m-d', $result->created_at));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/580
	 */
	public function testPasswordsStoreCorrectly()
	{
		$model = new UserModel();

		$pass = password_hash('secret123', PASSWORD_BCRYPT);

		$data = [
			'name'    => $pass,
			'email'   => 'foo@example.com',
			'country' => 'US',
		];

		$model->insert($data);

		$this->seeInDatabase('user', $data);
	}

	//--------------------------------------------------------------------

	public function testInsertEvent()
	{
		$model = new EventModel();

		$data = [
			'name'    => 'Foo',
			'email'   => 'foo@example.com',
			'country' => 'US',
			'deleted' => 0,
		];

		$model->insert($data);

		$this->assertTrue($model->hasToken('beforeInsert'));
		$this->assertTrue($model->hasToken('afterInsert'));
	}

	//--------------------------------------------------------------------

	public function testUpdateEvent()
	{
		$model = new EventModel();

		$data = [
			'name'    => 'Foo',
			'email'   => 'foo@example.com',
			'country' => 'US',
			'deleted' => 0,
		];

		$id = $model->insert($data);
		$model->update($id, $data);

		$this->assertTrue($model->hasToken('beforeUpdate'));
		$this->assertTrue($model->hasToken('afterUpdate'));
	}

	//--------------------------------------------------------------------

	public function testDeleteEvent()
	{
		$model = new EventModel();

		$model->delete(1);

		$this->assertTrue($model->hasToken('beforeDelete'));
		$this->assertTrue($model->hasToken('afterDelete'));
	}

	//--------------------------------------------------------------------

	public function testFindEvent()
	{
		$model = new EventModel();

		$model->find(1);

		$this->assertTrue($model->hasToken('beforeFind'));
		$this->assertTrue($model->hasToken('afterFind'));
	}

	public function testBeforeFindReturnsData()
	{
		$model                       = new EventModel();
		$model->beforeFindReturnData = true;

		$result = $model->find(1);

		$this->assertTrue($model->hasToken('beforeFind'));
		$this->assertEquals($result, 'foobar');
	}

	public function testBeforeFindReturnDataPreventsAfterFind()
	{
		$model                       = new EventModel();
		$model->beforeFindReturnData = true;

		$model->find(1);

		$this->assertFalse($model->hasToken('afterFind'));
	}

	//--------------------------------------------------------------------

	public function testAllowCallbacksFalsePreventsTriggers()
	{
		$model = new EventModel();

		$model->allowCallbacks(false)->find(1);

		$this->assertFalse($model->hasToken('afterFind'));
	}

	//--------------------------------------------------------------------

	public function testAllowCallbacksTrueFiresTriggers()
	{
		$model = new EventModel();
		$this->setPrivateProperty($model, 'allowCallbacks', false);

		$model->allowCallbacks(true)->find(1);

		$this->assertTrue($model->hasToken('afterFind'));
	}

	//--------------------------------------------------------------------

	public function testAllowCallbacksResetsAfterTrigger()
	{
		$model = new EventModel();

		$model->allowCallbacks(false)->find(1);
		$model->delete(1);

		$this->assertFalse($model->hasToken('afterFind'));
		$this->assertTrue($model->hasToken('afterDelete'));
	}

	//--------------------------------------------------------------------

	public function testAllowCallbacksUsesModelProperty()
	{
		$model = new EventModel();
		$this->setPrivateProperty($model, 'allowCallbacks', false);
		$this->setPrivateProperty($model, 'tempAllowCallbacks', false); // Was already set by the constructor

		$model->find(1);
		$model->delete(1);

		$this->assertFalse($model->hasToken('afterFind'));
		$this->assertFalse($model->hasToken('afterDelete'));
	}

	//--------------------------------------------------------------------

	public function testSetWorksWithInsert()
	{
		$model = new EventModel();

		$this->dontSeeInDatabase('user', [
			'email' => 'foo@example.com',
		]);

		$model->set([
			'email'   => 'foo@example.com',
			'name'    => 'Foo Bar',
			'country' => 'US',
		])
			  ->insert();

		$this->seeInDatabase('user', [
			'email' => 'foo@example.com',
		]);
	}

	//--------------------------------------------------------------------

	public function testSetWorksWithUpdate()
	{
		$model = new EventModel();

		$this->dontSeeInDatabase('user', [
			'email' => 'foo@example.com',
		]);

		$userId = $model->insert([
			'email'   => 'foo@example.com',
			'name'    => 'Foo Bar',
			'country' => 'US',
		]);

		$model->set([
			'name' => 'Fred Flintstone',
		])
			  ->update($userId);

		$this->seeInDatabase('user', [
			'id'    => $userId,
			'email' => 'foo@example.com',
			'name'  => 'Fred Flintstone',
		]);
	}

	//--------------------------------------------------------------------

	public function testSetWorksWithUpdateNoId()
	{
		$model = new EventModel();

		$this->dontSeeInDatabase('user', [
			'email' => 'foo@example.com',
		]);

		$userId = $model->insert([
			'email'   => 'foo@example.com',
			'name'    => 'Foo Bar',
			'country' => 'US',
		]);

		$model
			->where('id', $userId)
			->set([
				'name' => 'Fred Flintstone',
			])
			->update();

		$this->seeInDatabase('user', [
			'id'    => $userId,
			'email' => 'foo@example.com',
			'name'  => 'Fred Flintstone',
		]);
	}

	//--------------------------------------------------------------------

	public function testUpdateArray()
	{
		$model = new EventModel();

		$data = [
			'name'    => 'Foo',
			'email'   => 'foo@example.com',
			'country' => 'US',
			'deleted' => 0,
		];

		$id     = $model->insert($data);
		$result = $model->update([1, 2], ['name' => 'Foo Bar']);

		$this->assertTrue($result);

		$this->seeInDatabase('user', ['id' => 1, 'name' => 'Foo Bar']);
		$this->seeInDatabase('user', ['id' => 2, 'name' => 'Foo Bar']);
	}

	//--------------------------------------------------------------------

	public function testUpdateResultFail()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', false);

		$model = new EventModel();

		$data = [
			'name'    => 'Foo',
			'email'   => 'foo@example.com',
			'country' => 'US',
			'deleted' => 0,
		];

		$id = $model->insert($data);

		$this->setPrivateProperty($model, 'allowedFields', ['name123']);
		$result = $model->update(1, ['name123' => 'Foo Bar 1']);

		$this->assertFalse($result);

		$this->dontSeeInDatabase('user', ['id' => 1, 'name' => 'Foo Bar 1']);
	}

	//--------------------------------------------------------------------

	public function testInsertBatchSuccess()
	{
		$job_data = [
			[
				'name'        => 'Comedian',
				'description' => 'Theres something in your teeth',
			],
			[
				'name'        => 'Cab Driver',
				'description' => 'Iam yellow',
			],
		];

		$model = new JobModel($this->db);
		$model->insertBatch($job_data);

		$this->seeInDatabase('job', ['name' => 'Comedian']);
		$this->seeInDatabase('job', ['name' => 'Cab Driver']);
	}

	//--------------------------------------------------------------------

	public function testInsertBatchValidationFail()
	{
		$job_data = [
			[
				'name'        => 'Comedian',
				'description' => null,
			],
		];

		$model = new JobModel($this->db);

		$this->setPrivateProperty($model, 'validationRules', ['description' => 'required']);

		$this->assertFalse($model->insertBatch($job_data));

		$error = $model->errors();
		$this->assertTrue(isset($error['description']));
	}

	//--------------------------------------------------------------------

	public function testUpdateBatchSuccess()
	{
		$data = [
			[
				'name'    => 'Derek Jones',
				'country' => 'Greece',
			],
			[
				'name'    => 'Ahmadinejad',
				'country' => 'Greece',
			],
		];

		$model = new EventModel($this->db);

		$model->updateBatch($data, 'name');

		$this->seeInDatabase('user', [
			'name'    => 'Derek Jones',
			'country' => 'Greece',
		]);
		$this->seeInDatabase('user', [
			'name'    => 'Ahmadinejad',
			'country' => 'Greece',
		]);
	}

	//--------------------------------------------------------------------

	public function testUpdateBatchValidationFail()
	{
		$data = [
			[
				'name'    => 'Derek Jones',
				'country' => null,
			],
		];

		$model = new EventModel($this->db);
		$this->setPrivateProperty($model, 'validationRules', ['country' => 'required']);

		$this->assertFalse($model->updateBatch($data, 'name'));

		$error = $model->errors();
		$this->assertTrue(isset($error['country']));
	}

	//--------------------------------------------------------------------

	public function testUpdateBatchWithEntity()
	{
		$entity1 = new class extends Entity
		{
			protected $id;
			protected $name;
			protected $email;
			protected $country;
			protected $deleted;
			protected $created_at;
			protected $updated_at;

			protected $_options = [
				'datamap' => [],
				'dates'   => [
					'created_at',
					'updated_at',
					'deleted_at',
				],
				'casts'   => [],
			];
		};

		$entity2   = new class extends Entity
		{
			protected $id;
			protected $name;
			protected $email;
			protected $country;
			protected $deleted;
			protected $created_at;
			protected $updated_at;

			protected $_options = [
				'datamap' => [],
				'dates'   => [
					'created_at',
					'updated_at',
					'deleted_at',
				],
				'casts'   => [],
			];
		};
		$testModel = new UserModel();

		$entity1->id      = 1;
		$entity1->name    = 'Jones Martin';
		$entity1->country = 'India';
		$entity1->deleted = 0;

		$entity2->id      = 4;
		$entity2->name    = 'Jones Martin';
		$entity2->country = 'India';
		$entity2->deleted = 0;

		$this->assertEquals(2, $testModel->updateBatch([$entity1, $entity2], 'id'));
	}

	//--------------------------------------------------------------------

	public function testSelectAndEntitiesSaveOnlyChangedValues()
	{
		// Insert value in job table
		$this->hasInDatabase('job', [
			'name'        => 'Rocket Scientist',
			'description' => 'Plays guitar for Queen',
			'created_at'  => time(),
		]);

		$model = new EntityModel();

		// get only id, name column
		$job = $model->select('id, name')
					 ->where('name', 'Rocket Scientist')
					 ->first();

		// Hence getting Null as description column not in select clause
		$this->assertNull($job->description);

		// Equals with name to check, correct record fetched or not.
		$this->assertEquals('Rocket Scientist', $job->name);

		$job->description = 'Some guitar description';

		// saving the result set with description as empty
		$model->save($job);

		// check for the record to same entry exists or not
		$this->seeInDatabase('job', [
			'id'   => $job->id,
			'name' => 'Rocket Scientist',
		]);

		// select all columns from job table
		$job = $model->select('id, name, description')
					 ->where('name', 'Rocket Scientist')
					 ->first();

		// check whether the Null value successfully updated or not
		$this->assertEquals('Some guitar description', $job->description);
	}

	//--------------------------------------------------------------------

	public function testUpdateNoPrimaryKey()
	{
		$model = new SecondaryModel();

		$this->db->table('secondary')
				 ->insert([
					 'id'    => 1,
					 'key'   => 'foo',
					 'value' => 'bar',
				 ]);

		$this->dontSeeInDatabase('secondary', [
			'key'   => 'bar',
			'value' => 'baz',
		]);

		$model->where('key', 'foo')
			  ->update(null, ['key' => 'bar', 'value' => 'baz']);

		$this->seeInDatabase('secondary', [
			'key'   => 'bar',
			'value' => 'baz',
		]);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1617
	 */
	public function testCountAllResultsRespectsSoftDeletes()
	{
		$model = new UserModel();

		// testSeeder has 4 users....
		$this->assertEquals(4, $model->countAllResults());

		$model->where('name', 'Derek Jones')
			  ->delete();

		$this->assertEquals(3, $model->countAllResults());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1584
	 */
	public function testUpdateWithValidation()
	{
		$model = new ValidModel($this->db);

		$data = [
			'description' => 'This is a first test!',
			'name'        => 'valid',
			'id'          => 42,
			'token'       => 42,
		];

		$id = $model->insert($data);

		$this->assertTrue((bool)$id);

		$data['description'] = 'This is a second test!';
		unset($data['name']);

		$result = $model->update($id, $data);
		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1717
	 */
	public function testRequiredWithValidationEmptyString()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name' => '',
		];

		$this->assertFalse($model->insert($data));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1717
	 */
	public function testRequiredWithValidationNull()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name' => null,
		];

		$this->assertFalse($model->insert($data));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1717
	 */
	public function testRequiredWithValidationTrue()
	{
		$model = new ValidModel($this->db);

		$data = [
			'name'        => 'foobar',
			'description' => 'just because we have to',
		];

		$this->assertTrue($model->insert($data) !== false);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1574
	 */
	public function testValidationIncludingErrors()
	{
		$model = new ValidErrorsModel($this->db);

		$data = [
			'description' => 'This is a first test!',
			'name'        => 'valid',
			'id'          => 42,
			'token'       => 42,
		];

		$id = $model->insert($data);

		$this->assertFalse((bool)$id);

		$errors = $model->errors();

		$this->assertEquals('Minimum Length Error', $model->errors()['name']);
	}

	//--------------------------------------------------------------------

	public function testThrowsWithNoPrimaryKey()
	{
		$this->expectException('CodeIgniter\Exceptions\ModelException');
		$this->expectExceptionMessage('`Tests\Support\Models\UserModel` model class does not specify a Primary Key.');

		$model = new UserModel();
		$this->setPrivateProperty($model, 'primaryKey', '');

		$model->find(1);
	}

	//--------------------------------------------------------------------

	public function testThrowsWithNoDateFormat()
	{
		$this->expectException('CodeIgniter\Exceptions\ModelException');
		$this->expectExceptionMessage('`Tests\Support\Models\UserModel` model class does not have a valid dateFormat.');

		$model = new UserModel();
		$this->setPrivateProperty($model, 'dateFormat', '');

		$model->delete(1);
	}

	//--------------------------------------------------------------------

	public function testInsertID()
	{
		$model = new JobModel();

		$data = [
			'name'        => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$model->protect(false)
			  ->save($data);

		$lastInsertId = $model->getInsertID();

		$this->seeInDatabase('job', ['id' => $lastInsertId]);
	}

	//--------------------------------------------------------------------

	public function testInsertResult()
	{
		$model = new JobModel();

		$data = [
			'name'        => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$result = $model->protect(false)
			  ->insert($data, false);

		$this->assertTrue($result->resultID !== false);

		$lastInsertId = $model->getInsertID();

		$this->seeInDatabase('job', ['id' => $lastInsertId]);
	}

	//--------------------------------------------------------------------

	public function testInsertResultFail()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', false);

		$model = new JobModel();

		$data = [
			'name123'     => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$result = $model->protect(false)
			  ->insert($data, false);

		$this->assertFalse($result->resultID);

		$lastInsertId = $model->getInsertID();

		$this->assertEquals(0, $lastInsertId);

		$this->dontSeeInDatabase('job', ['id' => $lastInsertId]);
	}

	//--------------------------------------------------------------------

	public function testSetTable()
	{
		$model = new SecondaryModel();

		$model->setTable('job');

		$data = [
			'name'        => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$model->protect(false)
			  ->save($data);

		$lastInsertId = $model->getInsertID();

		$this->seeInDatabase('job', ['id' => $lastInsertId]);
	}

	//--------------------------------------------------------------------

	public function testPaginate()
	{
		$model = new ValidModel($this->db);

		$data = $model->paginate();

		$this->assertEquals(4, count($data));
	}

	//--------------------------------------------------------------------

	public function testPaginateChangeConfigPager()
	{
		$perPage                 = config('Pager')->perPage;
		config('Pager')->perPage = 1;

		$model = new ValidModel($this->db);

		$data = $model->paginate();

		$this->assertEquals(1, count($data));

		config('Pager')->perPage = $perPage;
	}

	//--------------------------------------------------------------------

	public function testPaginatePassPerPageParameter()
	{
		$model = new ValidModel($this->db);

		$data = $model->paginate(2);

		$this->assertEquals(2, count($data));
	}

	//--------------------------------------------------------------------

	public function testPaginateForQueryWithGroupBy()
	{
		$model = new ValidModel($this->db);
		$model->groupBy('id');

		$model->paginate();
		$this->assertEquals(4, $model->pager->getDetails()['total']);
	}

	//--------------------------------------------------------------------

	public function testPaginateWithDeleted()
	{
		$model = new UserModel($this->db);
		$model->delete(1);

		$data = $model->withDeleted()->paginate();

		$this->assertEquals(4, count($data));
		$this->assertEquals(4, $model->pager->getDetails()['total']);
	}

	//--------------------------------------------------------------------

	public function testPaginateWithoutDeleted()
	{
		$model = new UserModel($this->db);
		$model->delete(1);

		$data = $model->withDeleted(false)->paginate();

		$this->assertEquals(3, count($data));
		$this->assertEquals(3, $model->pager->getDetails()['total']);
	}

	//--------------------------------------------------------------------

	public function testValidationByObject()
	{
		$model = new ValidModel($this->db);

		$data = new class
		{
			public $name  = '';
			public $id    = '';
			public $token = '';
		};

		$data->name  = 'abc';
		$data->id    = '13';
		$data->token = '13';

		$this->assertTrue($model->validate($data));
	}

	//--------------------------------------------------------------------

	public function testGetValidationRules()
	{
		$model = new JobModel($this->db);

		$this->setPrivateProperty($model, 'validationRules', ['description' => 'required']);

		$rules = $model->getValidationRules();

		$this->assertEquals('required', $rules['description']);
	}

	//--------------------------------------------------------------------

	public function testGetValidationMessages()
	{
		$job_data = [
			[
				'name'        => 'Comedian',
				'description' => null,
			],
		];

		$model = new JobModel($this->db);

		$this->setPrivateProperty($model, 'validationRules', ['description' => 'required']);
		$this->setPrivateProperty($model, 'validationMessages', ['description' => 'Description field is required.']);

		$this->assertFalse($model->insertBatch($job_data));

		$error = $model->getValidationMessages();
		$this->assertEquals('Description field is required.', $error['description']);
	}

	//--------------------------------------------------------------------

	public function testGetGetModelDetails()
	{
		$model = new JobModel($this->db);

		$this->assertEquals('job', $model->table);
		$this->assertEquals('id', $model->primaryKey);
		$this->assertEquals('object', $model->returnType);
		$this->assertNull($model->DBGroup);
	}

	//--------------------------------------------------------------------

	public function testSaveObject()
	{
		$model = new ValidModel($this->db);

		$testModel = new JobModel();

		$testModel->name        = 'my name';
		$testModel->description = 'some description';

		$this->setPrivateProperty($model, 'useTimestamps', true);

		$model->insert($testModel);

		$lastInsertId = $model->getInsertID();

		$this->seeInDatabase('job', ['id' => $lastInsertId]);
	}

	//--------------------------------------------------------------------

	public function testEmptySaveData()
	{
		$model = new JobModel();

		$data = [];

		$data = $model->protect(false)
					  ->save($data);

		$this->assertTrue($data);
	}

	//--------------------------------------------------------------------

	public function testUpdateObject()
	{
		$model = new ValidModel($this->db);

		$testModel = new JobModel();

		$testModel->name        = 'my name';
		$testModel->description = 'some description';

		$this->setPrivateProperty($model, 'useTimestamps', true);

		$model->update(1, $testModel);

		$this->seeInDatabase('job', ['id' => 1]);
	}

	//--------------------------------------------------------------------

	public function testDeleteWithSoftDelete()
	{
		$model = new JobModel();

		$this->setPrivateProperty($model, 'useTimestamps', true);
		$this->setPrivateProperty($model, 'useSoftDeletes', true);

		$model->delete(1);

		$this->seeInDatabase('job', ['id' => 1, 'deleted_at IS NOT NULL' => null]);
	}

	//--------------------------------------------------------------------

	public function testPurgeDeletedWithSoftDeleteFalse()
	{
		$model = new JobModel();

		$this->db->table('job')
				 ->where('id', 1)
				 ->update(['deleted_at' => time()]);

		$model->purgeDeleted();

		$jobs = $model->findAll();

		$this->assertCount(4, $jobs);
	}

	//--------------------------------------------------------------------

	public function testReplaceObject()
	{
		$model = new ValidModel($this->db);

		$data = [
			'id'          => 1,
			'name'        => 'my name',
			'description' => 'some description',
		];

		$model->replace($data);

		$this->seeInDatabase('job', ['id' => 1, 'name' => 'my name']);
	}

	//--------------------------------------------------------------------

	public function testGetValidationMessagesForReplace()
	{
		$job_data = [
			'name'        => 'Comedian',
			'description' => null,
		];

		$model = new JobModel($this->db);

		$this->setPrivateProperty($model, 'validationRules', ['description' => 'required']);

		$this->assertFalse($model->replace($job_data));

		$error = $model->errors();
		$this->assertTrue(isset($error['description']));
	}

	//--------------------------------------------------------------------

	public function testInsertBatchNewEntityWithDateTime()
	{
		$entity    = new class extends Entity{
			protected $id;
			protected $name;
			protected $email;
			protected $country;
			protected $deleted;
			protected $created_at;
			protected $updated_at;

			protected $_options = [
				'datamap' => [],
				'dates'   => [
					'created_at',
					'updated_at',
					'deleted_at',
				],
				'casts'   => [],
			];
		};
		$testModel = new UserModel();

		$entity->name       = 'Mark';
		$entity->email      = 'mark@example.com';
		$entity->country    = 'India';
		$entity->deleted    = 0;
		$entity->created_at = new Time('now');

		$this->setPrivateProperty($testModel, 'useTimestamps', true);

		$this->assertEquals(2, $testModel->insertBatch([$entity, $entity]));
	}

	//--------------------------------------------------------------------

	public function testSaveNewEntityWithDateTime()
	{
		$entity    = new class extends Entity{
			protected $id;
			protected $name;
			protected $email;
			protected $country;
			protected $deleted;
			protected $created_at;
			protected $updated_at;

			protected $_options = [
				'datamap' => [],
				'dates'   => [
					'created_at',
					'updated_at',
					'deleted_at',
				],
				'casts'   => [],
			];
		};
		$testModel = new UserModel();

		$entity->name       = 'Mark';
		$entity->email      = 'mark@example.com';
		$entity->country    = 'India';
		$entity->deleted    = 0;
		$entity->created_at = new Time('now');

		$this->setPrivateProperty($testModel, 'useTimestamps', true);

		$this->assertTrue($testModel->save($entity));
	}

	//--------------------------------------------------------------------

	public function testSaveNewEntityWithDate()
	{
		$entity    = new class extends Entity
		{
			protected $id;
			protected $name;
			protected $created_at;
			protected $updated_at;
			protected $_options = [
				'datamap' => [],
				'dates'   => [
					'created_at',
					'updated_at',
					'deleted_at',
				],
				'casts'   => [],
			];
		};
		$testModel = new class extends Model
		{
			protected $table          = 'empty';
			protected $allowedFields  = [
				'name',
			];
			protected $returnType     = 'object';
			protected $useSoftDeletes = true;
			protected $dateFormat     = 'date';
			public $name              = '';
		};

		$entity->name       = 'Mark';
		$entity->created_at = new Time('now');

		$this->setPrivateProperty($testModel, 'useTimestamps', true);

		$this->assertTrue($testModel->save($entity));

		$testModel->truncate();
	}

	//--------------------------------------------------------------------

	public function testUndefinedEntityPropertyReturnsNull()
	{
		$entity = new class extends Entity {};

		$this->assertNull($entity->undefinedProperty);
	}

	//--------------------------------------------------------------------

	public function testInsertArrayWithNoDataException()
	{
		$model = new UserModel();
		$data  = [];
		$this->expectException(DataException::class);
		$this->expectExceptionMessage('There is no data to insert.');
		$model->insert($data);
	}

	//--------------------------------------------------------------------

	public function testUpdateArrayWithNoDataException()
	{
		$model = new EventModel();

		$data = [
			'name'    => 'Foo',
			'email'   => 'foo@example.com',
			'country' => 'US',
			'deleted' => 0,
		];

		$id = $model->insert($data);

		$data = [];

		$this->expectException(DataException::class);
		$this->expectExceptionMessage('There is no data to update.');

		$model->update($id, $data);
	}

	//--------------------------------------------------------------------

	public function testInsertObjectWithNoDataException()
	{
		$model = new UserModel();
		$data  = new \stdClass();
		$this->expectException(DataException::class);
		$this->expectExceptionMessage('There is no data to insert.');
		$model->insert($data);
	}

	//--------------------------------------------------------------------

	public function testUpdateObjectWithNoDataException()
	{
		$model = new EventModel();

		$data = (object) [
							 'name'    => 'Foo',
							 'email'   => 'foo@example.com',
							 'country' => 'US',
							 'deleted' => 0,
						 ];

		$id = $model->insert($data);

		$data = new \stdClass();

		$this->expectException(DataException::class);
		$this->expectExceptionMessage('There is no data to update.');

		$model->update($id, $data);
	}

	//--------------------------------------------------------------------

	public function testInvalidAllowedFieldException()
	{
		$model = new JobModel();

		$data = [
			'name'        => 'Apprentice',
			'description' => 'That thing you do.',
		];

		$this->setPrivateProperty($model, 'allowedFields', []);

		$this->expectException(DataException::class);
		$this->expectExceptionMessage('Allowed fields must be specified for model: Tests\Support\Models\JobModel');

		$model->save($data);
	}

	//--------------------------------------------------------------------

	public function testInvalidEventException()
	{
		$model = new EventModel();

		$data = [
			'name'    => 'Foo',
			'email'   => 'foo@example.com',
			'country' => 'US',
			'deleted' => 0,
		];

		$this->setPrivateProperty($model, 'beforeInsert', ['anotherBeforeInsertMethod']);

		$this->expectException(DataException::class);
		$this->expectExceptionMessage('anotherBeforeInsertMethod is not a valid Model Event callback.');

		$model->insert($data);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1881
	 */
	public function testSoftDeleteWithTableJoinsFindAll()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at' => null]);

		$results = $model->join('job', 'job.id = user.id')
			->findAll();

		// Just making sure it didn't throw ambiguous delete error
		$this->assertCount(4, $results);
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1881
	 */
	public function testSoftDeleteWithTableJoinsFind()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at' => null]);

		$results = $model->join('job', 'job.id = user.id')
						 ->find(1);

		// Just making sure it didn't throw ambiguous deleted error
		$this->assertEquals(1, $results->id);
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1881
	 */
	public function testSoftDeleteWithTableJoinsFirst()
	{
		$model = new UserModel();

		$this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at' => null]);

		$results = $model->join('job', 'job.id = user.id')
						 ->first(1);

		// Just making sure it didn't throw ambiguous deleted error
		$this->assertEquals(1, $results->id);
	}

	//--------------------------------------------------------------------

	public function testUseAutoIncrementSetToFalseInsertException()
	{
		$this->expectException(DataException::class);
		$this->expectExceptionMessage('There is no primary key defined when trying to make insert');

		$model = new WithoutAutoIncrementModel();

		$insert = [
			'value' => 'some different value',
		];

		$model->insert($insert);
	}

	public function testUseAutoIncrementSetToFalseInsert()
	{
		$model = new WithoutAutoIncrementModel();

		$insert = [
			'key'   => 'some_random_key',
			'value' => 'some different value',
		];

		$model->insert($insert);

		$this->assertEquals($insert['key'], $model->getInsertID());
		$this->seeInDatabase('without_auto_increment', $insert);
	}

	public function testUseAutoIncrementSetToFalseUpdate()
	{
		$model = new WithoutAutoIncrementModel();

		$key    = 'key';
		$update = [
			'value' => 'some different value',
		];

		$model->update($key, $update);

		$this->seeInDatabase('without_auto_increment', ['key' => $key, 'value' => $update['value']]);
	}

	public function testUseAutoIncrementSetToFalseSave()
	{
		$model = new WithoutAutoIncrementModel();

		$insert = [
			'key'   => 'some_random_key',
			'value' => 'some value',
		];

		$model->save($insert);

		$this->assertEquals($insert['key'], $model->getInsertID());
		$this->seeInDatabase('without_auto_increment', $insert);

		$update = [
			'key'   => 'some_random_key',
			'value' => 'some different value',
		];
		$model->save($update);

		$this->assertEquals($insert['key'], $model->getInsertID());
		$this->seeInDatabase('without_auto_increment', $update);
	}

	//--------------------------------------------------------------------

	public function testMagicIssetTrue()
	{
		$model = new UserModel();

		$this->assertTrue(isset($model->table));
	}

	public function testMagicIssetFalse()
	{
		$model = new UserModel();

		$this->assertFalse(isset($model->foobar));
	}

	public function testMagicIssetWithNewProperty()
	{
		$model = new UserModel();

		$model->flavor = 'chocolate';

		$this->assertTrue(isset($model->flavor));
	}

	public function testMagicIssetFromDb()
	{
		$model = new UserModel();

		$this->assertTrue(isset($model->DBPrefix));
	}

	public function testMagicIssetFromBuilder()
	{
		$model = new UserModel();

		$this->assertTrue(isset($model->QBNoEscape));
	}

	public function testMagicGet()
	{
		$model = new UserModel();

		$this->assertEquals('user', $model->table);
	}

	public function testMagicGetMissing()
	{
		$model = new UserModel();

		$this->assertNull($model->foobar);
	}

	public function testMagicGetFromDB()
	{
		$model = new UserModel();

		$this->assertEquals('utf8', $model->charset);
	}

	public function testMagicGetFromBuilder()
	{
		$model = new UserModel();

		$this->assertIsArray($model->QBNoEscape);
	}

	public function testUndefinedModelMethod()
	{
		$model = new UserModel($this->db);
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Call to undefined method Tests\Support\Models\UserModel::undefinedMethodCall');
		$model->undefinedMethodCall();
	}

	public function testUndefinedMethodInBuilder()
	{
		$model = new JobModel($this->db);

		$model->find(1);

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Call to undefined method Tests\Support\Models\JobModel::getBindings');

		$binds = $model->builder()
			->getBindings();
	}

	/**
	 * @dataProvider provideAggregateAndGroupBy
	 */
	public function testFirstRecoverTempUseSoftDeletes($aggregate, $groupBy)
	{
		$model = new UserModel($this->db);
		$model->delete(1);
		if ($aggregate)
		{
			$model->select('sum(id) as id');
		}

		if ($groupBy)
		{
			$model->groupBy('id');
		}

		$user = $model->withDeleted()->first();
		$this->assertEquals(1, $user->id);

		$user2 = $model->first();
		$this->assertEquals(2, $user2->id);
	}

	public function testcountAllResultsRecoverTempUseSoftDeletes()
	{
		$model = new UserModel($this->db);
		$model->delete(1);
		$this->assertEquals(4, $model->withDeleted()->countAllResults());
		$this->assertEquals(3, $model->countAllResults());
	}

	public function testcountAllResultsFalseWithDeletedTrue()
	{
		$builder     = new BaseBuilder('user', $this->db);
		$expectedSQL = $builder->testMode()->countAllResults();

		$model = new UserModel($this->db);
		$model->delete(1);

		$this->assertEquals(4, $model->withDeleted()->countAllResults(false));

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertFalse($this->getPrivateProperty($model, 'tempUseSoftDeletes'));

		$this->assertEquals(4, $model->countAllResults());

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertTrue($this->getPrivateProperty($model, 'tempUseSoftDeletes'));
	}

	public function testcountAllResultsFalseWithDeletedFalse()
	{
		$builder     = new BaseBuilder('user', $this->db);
		$expectedSQL = $builder->testMode()->where('user.deleted_at', null)->countAllResults();

		$model = new UserModel($this->db);
		$model->delete(1);

		$this->assertEquals(3, $model->withDeleted(false)->countAllResults(false));

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertFalse($this->getPrivateProperty($model, 'tempUseSoftDeletes'));

		$this->assertEquals(3, $model->countAllResults());

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertTrue($this->getPrivateProperty($model, 'tempUseSoftDeletes'));
	}

	public function testcountAllResultsFalseWithDeletedTrueUseSoftDeletesFalse()
	{
		$builder     = new BaseBuilder('user', $this->db);
		$expectedSQL = $builder->testMode()->countAllResults();

		$model = new UserModel($this->db);
		$model->delete(1);

		$this->setPrivateProperty($model, 'useSoftDeletes', false);

		$this->assertEquals(4, $model->withDeleted()->countAllResults(false));

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertFalse($this->getPrivateProperty($model, 'tempUseSoftDeletes'));

		$this->assertEquals(4, $model->countAllResults());

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertFalse($this->getPrivateProperty($model, 'tempUseSoftDeletes'));
	}

	public function testcountAllResultsFalseWithDeletedFalseUseSoftDeletesFalse()
	{
		$builder     = new BaseBuilder('user', $this->db);
		$expectedSQL = $builder->testMode()->where('user.deleted_at', null)->countAllResults();

		$model = new UserModel($this->db);
		$model->delete(1);

		$this->setPrivateProperty($model, 'useSoftDeletes', false);

		$this->assertEquals(3, $model->withDeleted(false)->countAllResults(false));

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertFalse($this->getPrivateProperty($model, 'tempUseSoftDeletes'));

		$this->assertEquals(3, $model->countAllResults());

		$this->assertEquals($expectedSQL, (string)$this->db->getLastQuery());

		$this->assertFalse($this->getPrivateProperty($model, 'tempUseSoftDeletes'));
	}

	public function testSetAllowedFields()
	{
		$allowed1 = [
			'id',
			'created_at',
		];
		$allowed2 = [
			'id',
			'updated_at',
		];

		$model = new class extends Model {
			protected $allowedFields = [
				'id',
				'created_at',
			];
		};

		$this->assertSame($allowed1, $this->getPrivateProperty($model, 'allowedFields'));

		$model->setAllowedFields($allowed2);
		$this->assertSame($allowed2, $this->getPrivateProperty($model, 'allowedFields'));
	}
}
