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

use CodeIgniter\Config\Factories;
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

    public function testConstructorWithString(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $this->assertInstanceOf(Fabricator::class, $fabricator);
    }

    public function testConstructorWithInstance(): void
    {
        $model = new UserModel();

        $fabricator = new Fabricator($model);

        $this->assertInstanceOf(Fabricator::class, $fabricator);
    }

    public function testConstructorWithInvalid(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(lang('Fabricator.invalidModel'));

        new Fabricator('SillyRabbit\Models\AreForKids');
    }

    public function testConstructorSetsFormatters(): void
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testConstructorGuessesFormatters(): void
    {
        $fabricator = new Fabricator(UserModel::class, null);

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testConstructorDefaultsToAppLocale(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $this->assertSame(config('App')->defaultLocale, $fabricator->getLocale());
    }

    public function testConstructorUsesProvidedLocale(): void
    {
        $locale = 'fr_FR';

        $fabricator = new Fabricator(UserModel::class, null, $locale);

        $this->assertSame($locale, $fabricator->getLocale());
    }

    public function testModelUsesNewInstance(): void
    {
        // Inject the wrong model for UserModel
        $mock = new FabricatorModel();
        Factories::injectMock('models', UserModel::class, $mock);

        $fabricator = new Fabricator(UserModel::class);

        // Fabricator gets the instance from Factories, so it is FabricatorModel.
        $this->assertInstanceOf(FabricatorModel::class, $fabricator->getModel());
        // But Fabricator creates a new instance.
        $this->assertNotSame($mock, $fabricator->getModel());
    }

    public function testGetModelReturnsModel(): void
    {
        $fabricator = new Fabricator(UserModel::class);
        $this->assertInstanceOf(UserModel::class, $fabricator->getModel());

        $model       = new UserModel();
        $fabricator2 = new Fabricator($model);
        $this->assertInstanceOf(UserModel::class, $fabricator2->getModel());
    }

    public function testGetFakerReturnsUsableGenerator(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $faker = $fabricator->getFaker();

        $this->assertIsNumeric($faker->randomDigit());
    }

    public function testSetFormattersChangesFormatters(): void
    {
        $formatters = ['boo' => 'hiss'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setFormatters($formatters);

        $this->assertSame($formatters, $fabricator->getFormatters());
    }

    public function testSetFormattersDetectsFormatters(): void
    {
        $formatters = ['boo' => 'hiss'];
        $fabricator = new Fabricator(UserModel::class, $formatters);

        $fabricator->setFormatters();

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testDetectFormattersDetectsFormatters(): void
    {
        $formatters = ['boo' => 'hiss'];
        $fabricator = new Fabricator(UserModel::class, $formatters);

        $method = $this->getPrivateMethodInvoker($fabricator, 'detectFormatters');

        $method();

        $this->assertSame($this->formatters, $fabricator->getFormatters());
    }

    public function testSetOverridesSets(): void
    {
        $overrides  = ['name' => 'Steve'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setOverrides($overrides);

        $this->assertSame($overrides, $fabricator->getOverrides());
    }

    public function testSetOverridesDefaultPersists(): void
    {
        $overrides  = ['name' => 'Steve'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setOverrides($overrides);
        $fabricator->getOverrides();

        $this->assertSame($overrides, $fabricator->getOverrides());
    }

    public function testSetOverridesOnce(): void
    {
        $overrides  = ['name' => 'Steve'];
        $fabricator = new Fabricator(UserModel::class);

        $fabricator->setOverrides($overrides, false);
        $fabricator->getOverrides();

        $this->assertSame([], $fabricator->getOverrides());
    }

    public function testGuessFormattersReturnsActual(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'catchPhrase';
        $formatter = $method($field);

        $this->assertSame($field, $formatter);
    }

    public function testGuessFormattersFieldReturnsDateFormat(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'created_at';
        $formatter = $method($field);

        $this->assertSame('date', $formatter);
    }

    public function testGuessFormattersPrimaryReturnsNumberBetween(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'id';
        $formatter = $method($field);

        $this->assertSame('numberBetween', $formatter);
    }

    public function testGuessFormattersMatchesPartial(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'business_email';
        $formatter = $method($field);

        $this->assertSame('email', $formatter);
    }

    public function testGuessFormattersFallback(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $method = $this->getPrivateMethodInvoker($fabricator, 'guessFormatter');

        $field     = 'zaboomafoo';
        $formatter = $method($field);

        $this->assertSame($fabricator->defaultFormatter, $formatter);
    }

    public function testMakeArrayReturnsArray(): void
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $result = $fabricator->makeArray();

        $this->assertIsArray($result);
    }

    public function testMakeArrayUsesOverrides(): void
    {
        $overrides = ['name' => 'The Admiral'];

        $fabricator = new Fabricator(UserModel::class, $this->formatters);
        $fabricator->setOverrides($overrides);

        $result = $fabricator->makeArray();

        $this->assertSame($overrides['name'], $result['name']);
    }

    public function testMakeArrayReturnsValidData(): void
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $result = $fabricator->makeArray();

        $this->assertSame($result['email'], filter_var($result['email'], FILTER_VALIDATE_EMAIL));
    }

    public function testMakeArrayUsesFakeMethod(): void
    {
        $fabricator = new Fabricator(FabricatorModel::class);

        $result = $fabricator->makeArray();

        $this->assertSame($result['name'], filter_var($result['name'], FILTER_VALIDATE_IP));
    }

    public function testMakeObjectReturnsModelReturnType(): void
    {
        $fabricator = new Fabricator(EntityModel::class);
        $expected   = $fabricator->getModel()->returnType;

        $result = $fabricator->makeObject();

        $this->assertInstanceOf($expected, $result);
    }

    public function testMakeObjectReturnsProvidedClass(): void
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);
        $className  = SimpleEntity::class;

        $result = $fabricator->makeObject($className);

        $this->assertInstanceOf($className, $result);
    }

    public function testMakeObjectReturnsStdClassForArrayReturnType(): void
    {
        $fabricator = new Fabricator(EventModel::class);

        $result = $fabricator->makeObject();

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testMakeObjectReturnsStdClassForObjectReturnType(): void
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $result = $fabricator->makeObject();

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testMakeObjectUsesOverrides(): void
    {
        $overrides = ['name' => 'The Admiral'];

        $fabricator = new Fabricator(UserModel::class, $this->formatters);
        $fabricator->setOverrides($overrides);

        $result = $fabricator->makeObject();

        $this->assertSame($overrides['name'], $result->name);
    }

    public function testMakeObjectReturnsValidData(): void
    {
        $fabricator = new Fabricator(UserModel::class, $this->formatters);

        $result = $fabricator->makeObject();

        $this->assertSame($result->email, filter_var($result->email, FILTER_VALIDATE_EMAIL));
    }

    public function testMakeObjectUsesFakeMethod(): void
    {
        $fabricator = new Fabricator(FabricatorModel::class);

        $result = $fabricator->makeObject();

        $this->assertSame($result->name, filter_var($result->name, FILTER_VALIDATE_IP));
    }

    public function testMakeReturnsSingleton(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $result = $fabricator->make();

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testMakeReturnsExpectedCount(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $count  = 10;
        $result = $fabricator->make($count);

        $this->assertIsArray($result);
        $this->assertCount($count, $result);
    }

    public function testCreateMockReturnsSingleton(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $result = $fabricator->create(null, true);

        $this->assertInstanceOf('stdClass', $result);
    }

    public function testCreateMockReturnsExpectedCount(): void
    {
        $fabricator = new Fabricator(UserModel::class);

        $count  = 10;
        $result = $fabricator->create($count, true);

        $this->assertIsArray($result);
        $this->assertCount($count, $result);
    }

    public function testCreateMockSetsDatabaseFields(): void
    {
        $fabricator = new Fabricator(FabricatorModel::class);

        $result = $fabricator->create(null, true);

        $this->assertIsInt($result->id);
        $this->assertIsInt($result->created_at);
        $this->assertIsInt($result->updated_at);

        $this->assertTrue(property_exists($result, 'deleted_at'));
        $this->assertNull($result->deleted_at);
    }

    public function testSetCountReturnsCount(): void
    {
        $result = Fabricator::setCount('goblins', 42);

        $this->assertSame(42, $result);
    }

    public function testSetCountSetsValue(): void
    {
        Fabricator::setCount('trolls', 3);
        $result = Fabricator::getCount('trolls');

        $this->assertSame(3, $result);
    }

    public function testGetCountNewTableReturnsZero(): void
    {
        $result = Fabricator::getCount('gremlins');

        $this->assertSame(0, $result);
    }

    public function testUpCountIncrementsValue(): void
    {
        Fabricator::setCount('orcs', 12);
        Fabricator::upCount('orcs');

        $this->assertSame(13, Fabricator::getCount('orcs'));
    }

    public function testUpCountReturnsValue(): void
    {
        Fabricator::setCount('hobgoblins', 12);
        $result = Fabricator::upCount('hobgoblins');

        $this->assertSame(13, $result);
    }

    public function testUpCountNewTableReturnsOne(): void
    {
        $result = Fabricator::upCount('ogres');

        $this->assertSame(1, $result);
    }

    public function testDownCountDecrementsValue(): void
    {
        Fabricator::setCount('orcs', 12);
        Fabricator::downCount('orcs');

        $this->assertSame(11, Fabricator::getCount('orcs'));
    }

    public function testDownCountReturnsValue(): void
    {
        Fabricator::setCount('hobgoblins', 12);
        $result = Fabricator::downCount('hobgoblins');

        $this->assertSame(11, $result);
    }

    public function testDownCountNewTableReturnsNegativeOne(): void
    {
        $result = Fabricator::downCount('ogres');

        $this->assertSame(-1, $result);
    }

    public function testResetClearsValue(): void
    {
        Fabricator::setCount('giants', 1000);
        Fabricator::resetCounts();

        $this->assertSame(0, Fabricator::getCount('giants'));
    }
}
