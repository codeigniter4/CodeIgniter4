<?php namespace CodeIgniter\Test;

use CodeIgniter\Database\ModelFactory;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\FabricatorModel;
use Tests\Support\Models\UserModel;

class FabricatorTest extends CIUnitTestCase
{
	/**
	 * Default formatters to use for UserModel. Should match detected version.
	 *
	 * @var array
	 */
	protected $formatters = [
		'name'       => 'name',
		'email'      => 'email',
		'country'    => 'country',
		'deleted_at' => 'date',
	];

	//--------------------------------------------------------------------

	public function testConstructorWithString()
	{
		$fabricator = new Fabricator(UserModel::class);

		$this->assertInstanceOf(Fabricator::class, $fabricator);
	}

	public function testConstructorWithInstance()
	{
		$model = new UserModel();

		$fabricator = new Fabricator($model);

		$this->assertInstanceOf(Fabricator::class, $fabricator);
	}

	public function testConstructorSetsFormatters()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);

		$this->assertEquals($this->formatters, $fabricator->getFormatters());
	}

	public function testConstructorGuessesFormatters()
	{
		$fabricator = new Fabricator(UserModel::class, null);

		$this->assertEquals($this->formatters, $fabricator->getFormatters());
	}

	public function testConstructorDefaultsToAppLocale()
	{
		$fabricator = new Fabricator(UserModel::class);

		$this->assertEquals(config('App')->defaultLocale, $fabricator->getLocale());
	}

	public function testConstructorUsesProvidedLocale()
	{
		$locale = 'fr_FR';

		$fabricator = new Fabricator(UserModel::class, null, $locale);

		$this->assertEquals($locale, $fabricator->getLocale());
	}

	//--------------------------------------------------------------------

	public function testModelUsesNewInstance()
	{
		// Inject the wrong model for UserModel to show it is ignored by Fabricator
		$mock = new FabricatorModel();
		ModelFactory::injectMock('Tests\Support\Models\UserModel', $mock);

		$fabricator = new Fabricator(UserModel::class);
		$this->assertInstanceOf(UserModel::class, $fabricator->getModel());
	}

	public function testGetModelReturnsModel()
	{
		$fabricator = new Fabricator(UserModel::class);
		$this->assertInstanceOf(UserModel::class, $fabricator->getModel());

		$model       = new UserModel();
		$fabricator2 = new Fabricator($model);
		$this->assertInstanceOf(UserModel::class, $fabricator2->getModel());
	}

	public function testGetFakerReturnsUsableGenerator()
	{
		$fabricator = new Fabricator(UserModel::class);

		$faker = $fabricator->getFaker();

		$this->assertIsNumeric($faker->randomDigit);
	}

	//--------------------------------------------------------------------

	public function testSetFormattersChangesFormatters()
	{
		$formatters = ['boo' => 'hiss'];
		$fabricator = new Fabricator(UserModel::class);

		$fabricator->setFormatters($formatters);

		$this->assertEquals($formatters, $fabricator->getFormatters());
	}

	public function testSetFormattersDetectsFormatters()
	{
		$formatters = ['boo' => 'hiss'];
		$fabricator = new Fabricator(UserModel::class, $formatters);

		$fabricator->setFormatters();

		$this->assertEquals($this->formatters, $fabricator->getFormatters());
	}

	public function testDetectFormattersDetectsFormatters()
	{
		$formatters = ['boo' => 'hiss'];
		$fabricator = new Fabricator(UserModel::class, $formatters);

		$method = $this->getPrivateMethodInvoker($fabricator, 'detectFormatters');

		$method();

		$this->assertEquals($this->formatters, $fabricator->getFormatters());
	}

	//--------------------------------------------------------------------

	public function testSetOverridesSets()
	{
		$overrides  = ['name' => 'Steve'];
		$fabricator = new Fabricator(UserModel::class);

		$fabricator->setOverrides($overrides);

		$this->assertEquals($overrides, $fabricator->getOverrides());
	}

	public function testSetOverridesDefaultPersists()
	{
		$overrides  = ['name' => 'Steve'];
		$fabricator = new Fabricator(UserModel::class);

		$fabricator->setOverrides($overrides);
		$fabricator->getOverrides();

		$this->assertEquals($overrides, $fabricator->getOverrides());
	}

	public function testSetOverridesOnce()
	{
		$overrides  = ['name' => 'Steve'];
		$fabricator = new Fabricator(UserModel::class);

		$fabricator->setOverrides($overrides, false);
		$fabricator->getOverrides();

		$this->assertEquals([], $fabricator->getOverrides());
	}

	//--------------------------------------------------------------------

	public function testGuessFormattersReturnsActual()
	{
		$fabricator = new Fabricator(UserModel::class);

		$method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

		$field     = 'catchPhrase';
		$formatter = $method($field);

		$this->assertEquals($field, $formatter);
	}

	public function testGuessFormattersFieldReturnsDateFormat()
	{
		$fabricator = new Fabricator(UserModel::class);

		$method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

		$field     = 'created_at';
		$formatter = $method($field);

		$this->assertEquals('date', $formatter);
	}

	public function testGuessFormattersPrimaryReturnsNumberBetween()
	{
		$fabricator = new Fabricator(UserModel::class);

		$method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

		$field     = 'id';
		$formatter = $method($field);

		$this->assertEquals('numberBetween', $formatter);
	}

	public function testGuessFormattersMatchesPartial()
	{
		$fabricator = new Fabricator(UserModel::class);

		$method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

		$field     = 'business_email';
		$formatter = $method($field);

		$this->assertEquals('email', $formatter);
	}

	public function testGuessFormattersFallback()
	{
		$fabricator = new Fabricator(UserModel::class);

		$method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

		$field     = 'zaboomafoo';
		$formatter = $method($field);

		$this->assertEquals($fabricator->defaultFormatter, $formatter);
	}

	//--------------------------------------------------------------------

	public function testMakeArrayReturnsArray()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);

		$result = $fabricator->makeArray();

		$this->assertIsArray($result);
	}

	public function testMakeArrayUsesOverrides()
	{
		$overrides = ['name' => 'The Admiral'];

		$fabricator = new Fabricator(UserModel::class, $this->formatters);
		$fabricator->setOverrides($overrides);

		$result = $fabricator->makeArray();

		$this->assertEquals($overrides['name'], $result['name']);
	}

	public function testMakeArrayReturnsValidData()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);

		$result = $fabricator->makeArray();

		$this->assertEquals($result['email'], filter_var($result['email'], FILTER_VALIDATE_EMAIL));
	}

	public function testMakeArrayUsesFakeMethod()
	{
		$fabricator = new Fabricator(FabricatorModel::class);

		$result = $fabricator->makeArray();

		$this->assertEquals($result['name'], filter_var($result['name'], FILTER_VALIDATE_IP));
	}

	//--------------------------------------------------------------------

	public function testMakeObjectReturnsModelReturnType()
	{
		$fabricator = new Fabricator(EntityModel::class);
		$expected   = $fabricator->getModel()->returnType;

		$result = $fabricator->makeObject();

		$this->assertInstanceOf($expected, $result);
	}

	public function testMakeObjectReturnsProvidedClass()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);
		$className  = 'Tests\Support\Models\SimpleEntity';

		$result = $fabricator->makeObject($className);

		$this->assertInstanceOf($className, $result);
	}

	public function testMakeObjectReturnsStdClassForArrayReturnType()
	{
		$fabricator = new Fabricator(EventModel::class);

		$result = $fabricator->makeObject();

		$this->assertInstanceOf(\stdClass::class, $result);
	}

	public function testMakeObjectReturnsStdClassForObjectReturnType()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);

		$result = $fabricator->makeObject();

		$this->assertInstanceOf(\stdClass::class, $result);
	}

	public function testMakeObjectUsesOverrides()
	{
		$overrides = ['name' => 'The Admiral'];

		$fabricator = new Fabricator(UserModel::class, $this->formatters);
		$fabricator->setOverrides($overrides);

		$result = $fabricator->makeObject();

		$this->assertEquals($overrides['name'], $result->name);
	}

	public function testMakeObjectReturnsValidData()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);

		$result = $fabricator->makeObject();

		$this->assertEquals($result->email, filter_var($result->email, FILTER_VALIDATE_EMAIL));
	}

	public function testMakeObjectUsesFakeMethod()
	{
		$fabricator = new Fabricator(FabricatorModel::class);

		$result = $fabricator->makeObject();

		$this->assertEquals($result->name, filter_var($result->name, FILTER_VALIDATE_IP));
	}

	//--------------------------------------------------------------------

	public function testMakeReturnsSingleton()
	{
		$fabricator = new Fabricator(UserModel::class);

		$result = $fabricator->make();

		$this->assertInstanceOf('stdClass', $result);
	}

	public function testMakeReturnsExpectedCount()
	{
		$fabricator = new Fabricator(UserModel::class);

		$count  = 10;
		$result = $fabricator->make($count);

		$this->assertIsArray($result);
		$this->assertCount($count, $result);
	}

	//--------------------------------------------------------------------

	public function testCreateMockReturnsSingleton()
	{
		$fabricator = new Fabricator(UserModel::class);

		$result = $fabricator->create(null, true);

		$this->assertInstanceOf('stdClass', $result);
	}

	public function testCreateMockReturnsExpectedCount()
	{
		$fabricator = new Fabricator(UserModel::class);

		$count  = 10;
		$result = $fabricator->create($count, true);

		$this->assertIsArray($result);
		$this->assertCount($count, $result);
	}

	public function testCreateMockSetsDatabaseFields()
	{
		$fabricator = new Fabricator(FabricatorModel::class);

		$result = $fabricator->create(null, true);

		$this->assertIsInt($result->id);
		$this->assertIsInt($result->created_at);
		$this->assertIsInt($result->updated_at);

		$this->assertObjectHasAttribute('deleted_at', $result);
		$this->assertNull($result->deleted_at);
	}
}
