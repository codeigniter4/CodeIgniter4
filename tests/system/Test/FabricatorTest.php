<?php namespace CodeIgniter\Test;

use CodeIgniter\Test\CIUnitTestCase;
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
		'deleted_at' => 'datetime',
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

		$this->assertEquals($fabricator->getModel()->dateFormat, $formatter);
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

		$method = $this->getPrivateMethodInvoker($fabricator, 'makeArray');
		$result = $method();

		$this->assertIsArray($result);
	}

	public function testMakeArrayUsesOverride()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);

		$override = ['name' => 'The Admiral'];

		$method = $this->getPrivateMethodInvoker($fabricator, 'makeArray');
		$result = $method($override);

		$this->assertEquals($override['name'], $result['name']);
	}

	public function testMakeArrayReturnsValidData()
	{
		$fabricator = new Fabricator(UserModel::class, $this->formatters);

		$method = $this->getPrivateMethodInvoker($fabricator, 'makeArray');
		$result = $method();

		$this->assertEquals($result['email'], filter_var($result['email'], FILTER_VALIDATE_EMAIL));
	}

	public function testMakeArrayUsesFakeMethod()
	{
		$fabricator = new Fabricator(FabricatorModel::class);

		$method = $this->getPrivateMethodInvoker($fabricator, 'makeArray');
		$result = $method();

		$this->assertEquals($result['name'], filter_var($result['name'], FILTER_VALIDATE_IP));
	}
}
