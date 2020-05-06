<?php namespace CodeIgniter\Test;

use CodeIgniter\Test\CIDatabaseTestCase;
use Tests\Support\Models\UserModel;

class FabricatorTest extends \CodeIgniter\Test\CIDatabaseTestCase
{
	public function testConstructor()
	{
		$fabricator = new Fabricator(UserModel::class);

		$this->assertInstanceOf('CodeIgniter\Test\Fabricator', $fabricator);
	}
}
