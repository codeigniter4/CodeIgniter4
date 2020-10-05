<?php

namespace CodeIgniter\Config;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Widgets\OtherWidget;
use Tests\Support\Widgets\SomeWidget;
use ReflectionClass;
use stdClass;

class FactoryTest extends CIUnitTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		Factory::reset();
	}

	protected function getFactoryStaticProperty(...$params)
	{
		// First parameter is the actual property
		$name = array_shift($params);

		$factory  = new ReflectionClass(Factory::class);
		$property = $factory->getStaticPropertyValue($name, 'ignoreMissing');

		if ($property === 'ignoreMissing')
		{
			return null;
		}

		// If any additional parameters were provided then drill into the array
		foreach ($params as $param)
		{
			$property = $property[$param];
		}

		return $property;
	}

	//--------------------------------------------------------------------

	public function testGetsConfigValues()
	{
		$result = Factory::getConfig('models');

		$this->assertTrue($result['prefersApp']);
	}

	public function testGetsConfigDefaults()
	{
		$result = Factory::getConfig('blahblahs');

		$this->assertTrue($result['prefersApp']);
		$this->assertEquals('Blahblahs', $result['path']);
	}

	public function testSetsConfigValues()
	{
		Factory::setConfig('widgets', ['foo' => 'bar']);

		$result = Factory::getConfig('widgets');

		$this->assertEquals('bar', $result['foo']);
		$this->assertEquals(true, $result['prefersApp']);
	}

	public function testUsesConfigFileValues()
	{
		// Simulate having a $widgets property in App\Config\Factories
		$config          = new Factories();
		$config->widgets = ['bar' => 'bam'];
		Factory::injectMock('config', Factories::class, $config);

		$result = Factory::getConfig('widgets');

		$this->assertEquals('bam', $result['bar']);
	}

	public function testSetConfigResets()
	{
		Factory::injectMock('widgets', 'Banana', new stdClass());

		$result = $this->getFactoryStaticProperty('instances');
		$this->assertArrayHasKey('widgets', $result);

		Factory::setConfig('widgets', []);

		$result = $this->getFactoryStaticProperty('instances');
		$this->assertArrayNotHasKey('widgets', $result);
	}

	public function testResetsAll()
	{
		Factory::setConfig('widgets', ['foo' => 'bar']);

		Factory::reset();

		$result = $this->getFactoryStaticProperty('configs');
		$this->assertEquals([], $result);
	}

	public function testResetsComponentOnly()
	{
		Factory::setConfig('widgets', ['foo' => 'bar']);
		Factory::setConfig('spigots', ['bar' => 'bam']);

		Factory::reset('spigots');

		$result = $this->getFactoryStaticProperty('configs');
		$this->assertArrayHasKey('widgets', $result);
	}

	//--------------------------------------------------------------------

	public function testGetsBasenameByBasename()
	{
		$this->assertEquals('SomeWidget', Factory::getBasename('SomeWidget'));
	}

	public function testGetsBasenameByClassname()
	{
		$this->assertEquals('SomeWidget', Factory::getBasename(SomeWidget::class));
	}

	public function testGetsBasenameInvalid()
	{
		$this->assertEquals('', Factory::getBasename('Tests\\Support\\'));
	}

	//--------------------------------------------------------------------

	public function testCreatesByBasename()
	{
		$result = Factory::widgets('SomeWidget', false);

		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testCreatesByClassname()
	{
		$result = Factory::widgets(SomeWidget::class, false);

		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testCreatesInvalid()
	{
		$result = Factory::widgets('gfnusvjai', false);

		$this->assertNull($result);
	}

	public function testIgnoresNonClass()
	{
		$result = Factory::widgets('NopeWidget', false);

		$this->assertNull($result);
	}

	public function testReturnsSharedInstance()
	{
		$widget1 = Factory::widgets('SomeWidget');
		$widget2 = Factory::widgets(SomeWidget::class);

		$this->assertSame($widget1, $widget2);
	}

	public function testInjection()
	{
		Factory::injectMock('widgets', 'Banana', new stdClass());

		$result = Factory::widgets('Banana');

		$this->assertInstanceOf(stdClass::class, $result);
	}

	//--------------------------------------------------------------------

	public function testRespectsComponentAlias()
	{
		Factory::setConfig('tedwigs', ['component' => 'widgets']);

		$result = Factory::tedwigs('SomeWidget');
		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testRespectsPath()
	{
		Factory::setConfig('models', ['path' => 'Widgets']);

		$result = Factory::models('SomeWidget');
		$this->assertInstanceOf(SomeWidget::class, $result);
	}

	public function testRespectsInstanceOf()
	{
		Factory::setConfig('widgets', ['instanceOf' => stdClass::class]);

		$result = Factory::widgets('SomeWidget');
		$this->assertInstanceOf(SomeWidget::class, $result);

		$result = Factory::widgets('OtherWidget');
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

		$result = Factory::widgets('OtherWidget');
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

		$result = Factory::widgets(OtherWidget::class);
		$this->assertInstanceOf(SomeWidget::class, $result);

		Factory::setConfig('widgets', ['prefersApp' => false]);

		$result = Factory::widgets(OtherWidget::class);
		$this->assertInstanceOf(OtherWidget::class, $result);
	}
}
