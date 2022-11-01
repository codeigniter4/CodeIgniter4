<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Database\ModelFactory;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\FabricatorModel;
use Tests\Support\Models\SimpleEntity;
use Tests\Support\Models\UserModel;

/**
 * @internal
 *
 * @group Others
 */
final class FabricatorTest extends CIUnitTestCase
{
    /**
     * Default formatters to use for UserModel. Should match detected version.
     */
    private array $formatters = [
        'name'       => 'name',
        'email'      => 'email',
        'country'    => 'country',
        'deleted_at' => 'date',
    ];

    protected function tearDown(): void
    {
        parent::tearDown();

        Fabricator::resetCounts();
    }

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

    public function testConstructorWithInvalid()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(lang('Fabricator.invalidModel'));

        new Fabricator('SillyRabbit\Models\AreForKids');
    }

    public function testConstructorSetsFormatters()
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testConstructorGuessesFormatters()
    {
        $fabricator = new Fabricator(UserModel::class, null);

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testConstructorDefaultsToAppLocale()
    {
        $fabricator = new Fabricator(UserModel::class);

        $this->assertSame(config('App')->defaultLocale, $fabricator->getLocale());
    }

    public function testConstructorUsesProvidedLocale()
    {
        $locale = 'fr_FR';

        $fabricator = new Fabricator(UserModel::class, null, $locale);

        $this->assertSame($locale, $fabricator->getLocale());
    }

    public function testModelUsesNewInstance()
    {
        // Inject the wrong model for UserModel to show it is ignored by Fabricator
        $mock = new FabricatorModel();
        ModelFactory::injectMock(UserModel::class, $mock);

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

        $this->assertIsNumeric($faker->randomDigit());
    }

    public function testSetFormattersChangesFormatters()
    {
        $formatters = ['boo' => 'hiss'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setFormatters($formatters);

        $this->assertSame($formatters, $fabricator->getFormatters());
    }

    public function testSetFormattersDetectsFormatters()
    {
        $formatters = ['boo' => 'hiss'];
        $fabricator = new Fabricator(UserModel::class, $formatters);

        $fabricator->setFormatters();

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testDetectFormattersDetectsFormatters()
    {
        $formatters = ['boo' => 'hiss'];
        $fabricator = new Fabricator(UserModel::class, $formatters);

        $method = $this->getPrivateMethodInvoker($fabricator, 'detectFormatters');

        $method();

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testSetOverridesSets()
    {
        $overrides  = ['name' => 'Steve'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setOverrides($overrides);

        $this->assertSame($overrides, $fabricator->getOverrides());
    }

    public function testSetOverridesDefaultPersists()
    {
        $overrides  = ['name' => 'Steve'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setOverrides($overrides);
        $fabricator->getOverrides();

        $this->assertSame($overrides, $fabricator->getOverrides());
    }

    public function testSetOverridesOnce()
    {
        $overrides  = ['name' => 'Steve'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setOverrides($overrides, false);
        $fabricator->getOverrides();

        $this->assertSame([], $fabricator->getOverrides());
    }

    public function testGuessFormattersReturnsActual()
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'catchPhrase';
        $formatter = $method($field);

        $this->assertSame($field, $formatter);
    }

    public function testGuessFormattersFieldReturnsDateFormat()
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'created_at';
        $formatter = $method($field);

        $this->assertSame('date', $formatter);
    }

    public function testGuessFormattersPrimaryReturnsNumberBetween()
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'id';
        $formatter = $method($field);

        $this->assertSame('numberBetween', $formatter);
    }

    public function testGuessFormattersMatchesPartial()
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'business_email';
        $formatter = $method($field);

        $this->assertSame('email', $formatter);
    }

    public function testGuessFormattersFallback()
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'zaboomafoo';
        $formatter = $method($field);

        $this->assertSame($fabricator->defaultFormatter, $formatter);
    }

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

        $this->assertSame($overrides['name'], $result['name']);
    }

    public function testMakeArrayReturnsValidData()
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $result = $fabricator->makeArray();

        $this->assertSame($result['email'], filter_var($result['email'], FILTER_VALIDATE_EMAIL));
    }

    public function testMakeArrayUsesFakeMethod()
    {
        $fabricator = new Fabricator(FabricatorModel::class);

        $result = $fabricator->makeArray();

        $this->assertSame($result['name'], filter_var($result['name'], FILTER_VALIDATE_IP));
    }

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
        $className  = SimpleEntity::class;

        $result = $fabricator->makeObject($className);

        $this->assertInstanceOf($className, $result);
    }

    public function testMakeObjectReturnsStdClassForArrayReturnType()
    {
        $fabricator = new Fabricator(EventModel::class);

        $result = $fabricator->makeObject();

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testMakeObjectReturnsStdClassForObjectReturnType()
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $result = $fabricator->makeObject();

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testMakeObjectUsesOverrides()
    {
        $overrides = ['name' => 'The Admiral'];

        $fabricator = new Fabricator(UserModel::class, $this->formatters);
        $fabricator->setOverrides($overrides);

        $result = $fabricator->makeObject();

        $this->assertSame($overrides['name'], $result->name);
    }

    public function testMakeObjectReturnsValidData()
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $result = $fabricator->makeObject();

        $this->assertSame($result->email, filter_var($result->email, FILTER_VALIDATE_EMAIL));
    }

    public function testMakeObjectUsesFakeMethod()
    {
        $fabricator = new Fabricator(FabricatorModel::class);

        $result = $fabricator->makeObject();

        $this->assertSame($result->name, filter_var($result->name, FILTER_VALIDATE_IP));
    }

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

    public function testSetCountReturnsCount()
    {
        $result = Fabricator::setCount('goblins', 42);

        $this->assertSame(42, $result);
    }

    public function testSetCountSetsValue()
    {
        Fabricator::setCount('trolls', 3);
        $result = Fabricator::getCount('trolls');

        $this->assertSame(3, $result);
    }

    public function testGetCountNewTableReturnsZero()
    {
        $result = Fabricator::getCount('gremlins');

        $this->assertSame(0, $result);
    }

    public function testUpCountIncrementsValue()
    {
        Fabricator::setCount('orcs', 12);
        Fabricator::upCount('orcs');

        $this->assertSame(13, Fabricator::getCount('orcs'));
    }

    public function testUpCountReturnsValue()
    {
        Fabricator::setCount('hobgoblins', 12);
        $result = Fabricator::upCount('hobgoblins');

        $this->assertSame(13, $result);
    }

    public function testUpCountNewTableReturnsOne()
    {
        $result = Fabricator::upCount('ogres');

        $this->assertSame(1, $result);
    }

    public function testDownCountDecrementsValue()
    {
        Fabricator::setCount('orcs', 12);
        Fabricator::downCount('orcs');

        $this->assertSame(11, Fabricator::getCount('orcs'));
    }

    public function testDownCountReturnsValue()
    {
        Fabricator::setCount('hobgoblins', 12);
        $result = Fabricator::downCount('hobgoblins');

        $this->assertSame(11, $result);
    }

    public function testDownCountNewTableReturnsNegativeOne()
    {
        $result = Fabricator::downCount('ogres');

        $this->assertSame(-1, $result);
    }

    public function testResetClearsValue()
    {
        Fabricator::setCount('giants', 1000);
        Fabricator::resetCounts();

        $this->assertSame(0, Fabricator::getCount('giants'));
    }
}
