<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;
use stdClass;
use Tests\Support\Models\UserModel;
use Tests\Support\Widgets\OtherWidget;
use Tests\Support\Widgets\SomeWidget;

/**
 * @internal
 *
 * @group Others
 */
final class FactoriesTest extends CIUnitTestCase
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
        foreach ($params as $param) {
            $property = $property[$param];
        }

        return $property;
    }

    public function testGetsOptions(): void
    {
        $result = Factories::getOptions('models');

        $this->assertTrue($result['preferApp']);
    }

    public function testGetsDefaultOptions(): void
    {
        $result = Factories::getOptions('blahblahs');

        $this->assertTrue($result['preferApp']);
        $this->assertSame('Blahblahs', $result['path']);
    }

    public function testSetsOptions(): void
    {
        Factories::setOptions('widgets', ['foo' => 'bar']);

        $result = Factories::getOptions('widgets');

        $this->assertSame('bar', $result['foo']);
        $this->assertTrue($result['preferApp']);
    }

    public function testUsesConfigOptions(): void
    {
        // Simulate having a $widgets property in App\Config\Factory
        $config = new class () extends Factory {
            public $widgets = ['bar' => 'bam'];
        };
        Factories::injectMock('config', Factory::class, $config);

        $result = Factories::getOptions('widgets');

        $this->assertSame('bam', $result['bar']);
    }

    public function testSetOptionsResets(): void
    {
        Factories::injectMock('widgets', 'Banana', new stdClass());

        $result = $this->getFactoriesStaticProperty('instances');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('widgets', $result);

        Factories::setOptions('widgets', []);

        $result = $this->getFactoriesStaticProperty('instances');
        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('widgets', $result);
    }

    public function testResetsAll(): void
    {
        Factories::setOptions('widgets', ['foo' => 'bar']);

        Factories::reset();

        $result = $this->getFactoriesStaticProperty('options');
        $this->assertSame([], $result);
    }

    public function testResetsComponentOnly(): void
    {
        Factories::setOptions('widgets', ['foo' => 'bar']);
        Factories::setOptions('spigots', ['bar' => 'bam']);

        Factories::reset('spigots');

        $result = $this->getFactoriesStaticProperty('options');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('widgets', $result);
    }

    public function testGetsBasenameByBasename(): void
    {
        $this->assertSame('SomeWidget', Factories::getBasename('SomeWidget'));
    }

    public function testGetsBasenameByClassname(): void
    {
        $this->assertSame('SomeWidget', Factories::getBasename(SomeWidget::class));
    }

    public function testGetsBasenameByAbsoluteClassname(): void
    {
        $this->assertSame('UserModel', Factories::getBasename(UserModel::class));
    }

    public function testGetsBasenameInvalid(): void
    {
        $this->assertSame('', Factories::getBasename('Tests\\Support\\'));
    }

    public function testCreatesByBasename(): void
    {
        $result = Factories::widgets('SomeWidget', ['getShared' => false]);

        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testCreatesByClassname(): void
    {
        $result = Factories::widgets(SomeWidget::class, ['getShared' => false]);

        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testCreatesByAbsoluteClassname(): void
    {
        $result = Factories::models(UserModel::class, ['getShared' => false]);

        $this->assertInstanceOf(UserModel::class, $result);
    }

    public function testCreatesInvalid(): void
    {
        $result = Factories::widgets('gfnusvjai', ['getShared' => false]);

        $this->assertNull($result);
    }

    public function testIgnoresNonClass(): void
    {
        $result = Factories::widgets('NopeWidget', ['getShared' => false]);

        $this->assertNull($result);
    }

    public function testReturnsSharedInstance(): void
    {
        $widget1 = Factories::widgets('SomeWidget');
        $widget2 = Factories::widgets(SomeWidget::class);

        $this->assertSame($widget1, $widget2);
    }

    public function testInjection(): void
    {
        Factories::injectMock('widgets', 'Banana', new stdClass());

        $result = Factories::widgets('Banana');

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testRespectsComponentAlias(): void
    {
        Factories::setOptions('tedwigs', ['component' => 'widgets']);

        $result = Factories::tedwigs('SomeWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testRespectsPath(): void
    {
        Factories::setOptions('models', ['path' => 'Widgets']);

        $result = Factories::models('SomeWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testRespectsInstanceOf(): void
    {
        Factories::setOptions('widgets', ['instanceOf' => 'stdClass']);

        $result = Factories::widgets('SomeWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);

        $result = Factories::widgets('OtherWidget');
        $this->assertNull($result);
    }

    public function testSharedRespectsInstanceOf(): void
    {
        Factories::injectMock('widgets', 'SomeWidget', new OtherWidget());

        $result = Factories::widgets('SomeWidget', ['instanceOf' => 'stdClass']);
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testPrioritizesParameterOptions(): void
    {
        Factories::setOptions('widgets', ['instanceOf' => 'stdClass']);

        $result = Factories::widgets('OtherWidget', ['instanceOf' => null]);
        $this->assertInstanceOf(OtherWidget::class, $result);
    }

    public function testFindsAppFirst(): void
    {
        // Create a fake class in App
        $class = 'App\Widgets\OtherWidget';
        if (! class_exists($class)) {
            class_alias(SomeWidget::class, $class);
        }

        $result = Factories::widgets('OtherWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testpreferAppOverridesClassname(): void
    {
        // Create a fake class in App
        $class = 'App\Widgets\OtherWidget';
        if (! class_exists($class)) {
            class_alias(SomeWidget::class, $class);
        }

        $result = Factories::widgets(OtherWidget::class);
        $this->assertInstanceOf(SomeWidget::class, $result);

        Factories::setOptions('widgets', ['preferApp' => false]);

        $result = Factories::widgets(OtherWidget::class);
        $this->assertInstanceOf(OtherWidget::class, $result);
    }
}
