<?php namespace CodeIgniter\Database;

use Tests\Support\Models\JobModel;
use CodeIgniter\Test\CIDatabaseTestCase;

class ModelFactoryTest extends CIDatabaseTestCase
{

	public function testCreateSeparateInstances()
	{
		$model          = ModelFactory::get('JobModel', false);
		$namespaceModel = ModelFactory::get('Tests\\Support\\Models\\JobModel', false);

		$this->assertInstanceOf(JobModel::class, $model);
		$this->assertInstanceOf(JobModel::class, $namespaceModel);
		$this->assertNotSame($model, $namespaceModel);
	}

	public function testCreateSharedInstance()
	{
		$model          = ModelFactory::get('JobModel', true);
		$namespaceModel = ModelFactory::get('Tests\\Support\\Models\\JobModel', true);

		$this->assertSame($model, $namespaceModel);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testInjection()
	{
		ModelFactory::reset();
		ModelFactory::injectMock('Banana', '\stdClass');
		$this->assertNotNull(ModelFactory::get('Banana'));
	}

}
