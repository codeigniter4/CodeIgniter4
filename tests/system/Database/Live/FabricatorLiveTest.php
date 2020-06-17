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

		// Some countries violate the 40 character limit so override that
		$fabricator->setOverrides(['country' => 'Spain']);

		$result = $fabricator->create();

		$this->seeInDatabase('user', ['name' => $result->name]);
	}

	public function testCreateAddsCountToDatabase()
	{
		$count = 10;

		$fabricator = new Fabricator(UserModel::class);

		// Some countries violate the 40 character limit so override that
		$fabricator->setOverrides(['country' => 'France']);

		$result = $fabricator->create($count);

		$this->seeNumRecords($count, 'user', []);
	}

	public function testHelperCreates()
	{
		helper('test');

		$result = fake(UserModel::class);

		$this->seeInDatabase('user', ['name' => $result->name]);
	}
}
