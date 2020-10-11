<?php

namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;
use stdClass;
use Tests\Support\Widgets\OtherWidget;
use Tests\Support\Widgets\SomeWidget;

class FactoriesTest extends CIUnitTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		Factories::reset();
	}

	protected function getFactoriesStaticProperty(...$params)
	{
		// First parameter is the actual property
		$name = array_shift($params);

		$factory    = new ReflectionClass(Factories::class);
		$properties = $factory->getStaticProperties();
		$property   = $properties[$name] ?? [];

		// If any additional parameters were provided then drill into the array
		foreach ($params as $param)
		{
			$property = $property[$param];
		}

		return $property;
	}

	public function testGetsConfigValues()
	{
		$result = Factories::getConfig('models');

		$this->assertTrue($result['prefersApp']);
	}

	public function testGetsConfigDefaults()
	{
		$result = Factories::getConfig('blahblahs');

		$this->assertTrue($result['prefersApp']);
		$this->assertEquals('Blahblahs', $result['path']);
	}

	public function testSetsConfigValues()
	{
		Factories::setConfig('widgets', ['foo' => 'bar']);

		$result = Factories::getConfig('widgets');

		$this->assertEquals('bar', $result['foo']);
		$this->assertTrue($result['prefersApp']);
	}

	public function testUsesConfigFileValues()
	{
		// Simulate having a $widgets property in App\Config\Factory
		$config          = new Factory();
		$config->widgets = ['bar' => 'bam'];
		Factories::injectMock('config', Factory::class, $config);

		$result = Factories::getConfig('widgets');

		$this->assertEquals('bam', $result['bar']);
	}

	public function testSetConfigResets()
	{
		Factories::injectMock('widgets', 'Banana', new stdClass());

		$result = $this->getFactoriesStaticProperty('instances');
		$this->assertIsArray($result);
		$this->assertArrayHasKey('widgets', $result);

		Factories::setConfig('widgets', []);

		$result = $this->getFactoriesStaticProperty('instances');
		$this->assertIsArray($result);
		$this->assertArrayNotHasKey('widgets', $result);
	}

	public function testResetsAll()
	{
		Factories::setConfig('widgets', ['foo' => 'bar']);

		Factories::reset();

		$result = $this->getFactoriesStaticProperty('configs');
		$this->assertEquals([], $result);
	}

	public function testResetsComponentOnly()
	{
		Factories::setConfig('widgets', ['foo' => 'bar']);
		Factories::setConfig('spigots', ['bar' => 'bam']);

		Factories::reset('spigots');

		$result = $this->getFactoriesStaticProperty('configs');
		$this->assertIsArray($result);
		$this->assertArrayHasKey('widgets', $result);
	}

	public function testGetsBasenameByBasename()
	{
		$this->assertEquals('SomeWidget', Factories::getBasename('SomeWidget'));
	}

	public function testGetsBasenameByClassname()
	{
		$this->assertEquals('SomeWidget', Factories::getBasename(SomeWidget::class));
	}

	public function testGetsBasenameByAbsoluteClassname()
	{
		$this->assertEquals('UserModel', Factories::getBasename('\Tests\Support\Models\UserModel'));
	}

	public function testGetsBasenameInvalid()
	{
		$this->assertEquals('', Factories::getBasename('Tests\\Support\\'));
	}

	public function testCreatesByBasename()
	{
		$result = Factories::widgets('SomeWidget', false);

		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testCreatesByClassname()
	{
		$result = Factories::widgets(SomeWidget::class, false);

		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testCreatesByAbsoluteClassname()
	{
		$result = Factories::models('\Tests\Support\Models\UserModel', false);

		$this->assertInstanceOf('Tests\Support\Models\UserModel', $result);
	}

	public function testCreatesInvalid()
	{
		$result = Factories::widgets('gfnusvjai', false);

		$this->assertNull($result);
	}

	public function testIgnoresNonClass()
	{
		$result = Factories::widgets('NopeWidget', false);

		$this->assertNull($result);
	}

	public function testReturnsSharedInstance()
	{
		$widget1 = Factories::widgets('SomeWidget');
		$widget2 = Factories::widgets(SomeWidget::class);

		$this->assertSame($widget1, $widget2);
	}

	public function testInjection()
	{
		Factories::injectMock('widgets', 'Banana', new stdClass());

		$result = Factories::widgets('Banana');

		$this->assertInstanceOf(stdClass::class, $result);
	}

	public function testRespectsComponentAlias()
	{
		Factories::setConfig('tedwigs', ['component' => 'widgets']);

		$result = Factories::tedwigs('SomeWidget');
		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testRespectsPath()
	{
		Factories::setConfig('models', ['path' => 'Widgets']);

		$result = Factories::models('SomeWidget');
		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testRespectsInstanceOf()
	{
		Factories::setConfig('widgets', ['instanceOf' => stdClass::class]);

		$result = Factories::widgets('SomeWidget');
		$this->assertInstanceOf(SomeWidget::class, $result);

		$result = Factories::widgets('OtherWidget');
		$this->assertNull($result);
	}

	public function testFindsAppFirst()
	{
		// Create a fake class in App
		$class = 'App\Widgets\OtherWidget';

		if (! class_exists($class))
		{
			class_alias(SomeWidget::class, $class);
		}

		$result = Factories::widgets('OtherWidget');
		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testPrefersAppOverridesClassname()
	{
		// Create a fake class in App
		$class = 'App\Widgets\OtherWidget';

		if (! class_exists($class))
		{
			class_alias(SomeWidget::class, $class);
		}

		$result = Factories::widgets(OtherWidget::class);
		$this->assertInstanceOf(SomeWidget::class, $result);

		Factories::setConfig('widgets', ['prefersApp' => false]);

		$result = Factories::widgets(OtherWidget::class);
		$this->assertInstanceOf(OtherWidget::class, $result);
	}
}
