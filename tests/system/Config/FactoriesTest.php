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
use InvalidArgumentException;
use ReflectionClass;
use stdClass;
use Tests\Support\Config\TestRegistrar;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\UserModel;
use Tests\Support\View\SampleClass;
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

    public function testGetsOptions()
    {
        $result = Factories::getOptions('models');

        $this->assertTrue($result['preferApp']);
    }

    public function testGetsDefaultOptions()
    {
        $result = Factories::getOptions('blahblahs');

        $this->assertTrue($result['preferApp']);
        $this->assertSame('Blahblahs', $result['path']);
    }

    public function testSetsOptions()
    {
        Factories::setOptions('widgets', ['foo' => 'bar']);

        $result = Factories::getOptions('widgets');

        $this->assertSame('bar', $result['foo']);
        $this->assertTrue($result['preferApp']);
    }

    public function testUsesConfigOptions()
    {
        // Simulate having a $widgets property in App\Config\Factory
        $config = new class () extends Factory {
            public $widgets = ['bar' => 'bam'];
        };
        Factories::injectMock('config', Factory::class, $config);

        $result = Factories::getOptions('widgets');

        $this->assertSame('bam', $result['bar']);
    }

    public function testSetOptionsResets()
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

    public function testResetsAll()
    {
        Factories::setOptions('widgets', ['foo' => 'bar']);

        Factories::reset();

        $result = $this->getFactoriesStaticProperty('options');
        $this->assertSame([], $result);
    }

    public function testResetsComponentOnly()
    {
        Factories::setOptions('widgets', ['foo' => 'bar']);
        Factories::setOptions('spigots', ['bar' => 'bam']);

        Factories::reset('spigots');

        $result = $this->getFactoriesStaticProperty('options');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('widgets', $result);
    }

    public function testGetsBasenameByBasename()
    {
        $this->assertSame('SomeWidget', Factories::getBasename('SomeWidget'));
    }

    public function testGetsBasenameByClassname()
    {
        $this->assertSame('SomeWidget', Factories::getBasename(SomeWidget::class));
    }

    public function testGetsBasenameByAbsoluteClassname()
    {
        $this->assertSame('UserModel', Factories::getBasename(UserModel::class));
    }

    public function testGetsBasenameInvalid()
    {
        $this->assertSame('', Factories::getBasename('Tests\\Support\\'));
    }

    public function testCreatesByBasename()
    {
        $result = Factories::widgets('SomeWidget', ['getShared' => false]);

        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testCreatesByClassname()
    {
        $result = Factories::widgets(SomeWidget::class, ['getShared' => false]);

        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testCreatesByAbsoluteClassname()
    {
        $result = Factories::models(UserModel::class, ['getShared' => false]);

        $this->assertInstanceOf(UserModel::class, $result);
    }

    public function testCreatesInvalid()
    {
        $result = Factories::widgets('gfnusvjai', ['getShared' => false]);

        $this->assertNull($result);
    }

    public function testIgnoresNonClass()
    {
        $result = Factories::widgets('NopeWidget', ['getShared' => false]);

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

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testRespectsComponentAlias()
    {
        Factories::setOptions('tedwigs', ['component' => 'widgets']);

        $result = Factories::tedwigs('SomeWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testRespectsPath()
    {
        Factories::setOptions('models', ['path' => 'Widgets']);

        $result = Factories::models('SomeWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testRespectsInstanceOf()
    {
        Factories::setOptions('widgets', ['instanceOf' => 'stdClass']);

        $result = Factories::widgets('SomeWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);

        $result = Factories::widgets('OtherWidget');
        $this->assertNull($result);
    }

    public function testSharedRespectsInstanceOf()
    {
        Factories::injectMock('widgets', 'SomeWidget', new OtherWidget());

        $result = Factories::widgets('SomeWidget', ['instanceOf' => 'stdClass']);
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testPrioritizesParameterOptions()
    {
        Factories::setOptions('widgets', ['instanceOf' => 'stdClass']);

        $result = Factories::widgets('OtherWidget', ['instanceOf' => null]);
        $this->assertInstanceOf(OtherWidget::class, $result);
    }

    public function testFindsAppFirst()
    {
        // Create a fake class in App
        $class = 'App\Widgets\OtherWidget';
        if (! class_exists($class)) {
            class_alias(SomeWidget::class, $class);
        }

        $result = Factories::widgets('OtherWidget');
        $this->assertInstanceOf(SomeWidget::class, $result);
    }

    public function testShortnameReturnsConfigInApp()
    {
        // Create a config class in App
        $file   = APPPATH . 'Config/TestRegistrar.php';
        $source = <<<'EOL'
            <?php
            namespace Config;
            class TestRegistrar
            {}
            EOL;
        file_put_contents($file, $source);

        $result = Factories::config('TestRegistrar');

        $this->assertInstanceOf('Config\TestRegistrar', $result);

        // Delete the config class in App
        unlink($file);
    }

    public function testFullClassnameIgnoresPreferApp()
    {
        // Create a config class in App
        $file   = APPPATH . 'Config/TestRegistrar.php';
        $source = <<<'EOL'
            <?php
            namespace Config;
            class TestRegistrar
            {}
            EOL;
        file_put_contents($file, $source);

        $result = Factories::config(TestRegistrar::class);

        $this->assertInstanceOf(TestRegistrar::class, $result);

        Factories::setOptions('config', ['preferApp' => false]);

        $result = Factories::config(TestRegistrar::class);

        $this->assertInstanceOf(TestRegistrar::class, $result);

        // Delete the config class in App
        unlink($file);
    }

    public function testPreferAppIsIgnored()
    {
        // Create a fake class in App
        $class = 'App\Widgets\OtherWidget';
        if (! class_exists($class)) {
            class_alias(SomeWidget::class, $class);
        }

        $result = Factories::widgets(OtherWidget::class);
        $this->assertInstanceOf(OtherWidget::class, $result);
    }

    public function testCanLoadTwoCellsWithSameShortName()
    {
        $cell1 = Factories::cells('\\' . SampleClass::class);
        $cell2 = Factories::cells('\\' . \Tests\Support\View\OtherCells\SampleClass::class);

        $this->assertNotSame($cell1, $cell2);
    }

    public function testDefineTwice()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Already defined in Factories: models CodeIgniter\Shield\Models\UserModel -> Tests\Support\Models\UserModel'
        );

        Factories::define(
            'models',
            'CodeIgniter\Shield\Models\UserModel',
            UserModel::class
        );
        Factories::define(
            'models',
            'CodeIgniter\Shield\Models\UserModel',
            EntityModel::class
        );
    }

    public function testDefineNonExistentClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No such class: App\Models\UserModel');

        Factories::define(
            'models',
            'CodeIgniter\Shield\Models\UserModel',
            'App\Models\UserModel'
        );
    }

    public function testDefineAfterLoading()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Already defined in Factories: models Tests\Support\Models\UserModel -> Tests\Support\Models\UserModel'
        );

        model(UserModel::class);

        Factories::define(
            'models',
            UserModel::class,
            'App\Models\UserModel'
        );
    }

    public function testDefineAndLoad()
    {
        Factories::define(
            'models',
            UserModel::class,
            EntityModel::class
        );

        $model = model(UserModel::class);

        $this->assertInstanceOf(EntityModel::class, $model);
    }
}
