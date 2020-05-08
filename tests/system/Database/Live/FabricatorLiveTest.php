<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\Fabricator;
use Tests\Support\Models\UserModel;

/**
 * @group DatabaseLive
 */
class FabricatorLiveTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	public function testCreateAddsToDatabase()
	{
		$fabricator = new Fabricator(UserModel::class);

		$result = $fabricator->create();

		$this->seeInDatabase('user', ['name' => $result->name]);
	}

	public function testCreateAddsCountToDatabase()
	{
		$count = 10;

		$fabricator = new Fabricator(UserModel::class);

		$result = $fabricator->create($count);

		$this->seeNumRecords($count, 'user', []);
	}

}
