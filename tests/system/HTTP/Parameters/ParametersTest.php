<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Parameters;

use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class ParametersTest extends CIUnitTestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $original = [];

    protected function setUp(): void
    {
        $this->original = [
            'DOCUMENT_ROOT'      => '',
            'DISPLAY'            => '1',
            'SCRIPT_NAME'        => 'vendor/bin/phpunit',
            'REQUEST_TIME_FLOAT' => 1741522635.661474,
            'IS_HTTPS'           => true,
            'PHP_NULLABLE_VAR'   => null,
            'OBJECT'             => new stdClass(),
            'argc'               => 3,
            'argv'               => [
                0 => 'vendor/bin/phpunit',
                1 => './tests/ParametersTest.php',
                2 => '--no-coverage',
            ],
        ];
    }

    public function testCreateParametersAndCompareIdentity(): void
    {
        $parameters = new Parameters($this->original);

        $this->assertSame($this->original, $parameters->all());
        $this->assertSame($this->original, iterator_to_array($parameters->getIterator()));
    }

    public function testCreateEmptyParameters(): void
    {
        $parameters = new Parameters();

        $this->assertSame([], $parameters->all());
        $this->assertSame([], $parameters->keys());
    }

    public function testGetValues(): void
    {
        $parameters = new Parameters($this->original);

        foreach ($parameters->keys() as $key) {
            $this->assertSame($this->original[$key], $parameters->get($key));
        }
    }

    public function testUpdateAndSetNewValues(): void
    {
        /**
         * @var array<string, mixed>
         */
        $expected = [
            'DOCUMENT_ROOT'      => '/www',
            'DISPLAY'            => '0',
            'SCRIPT_NAME'        => '',
            'REQUEST_TIME_FLOAT' => 1741522600.661400,
            'IS_HTTPS'           => false,
            'PHP_NULLABLE_VAR'   => null,
            'OBJECT'             => new stdClass(),
            'argc'               => 2,
            'argv'               => [
                0 => 'bin/phpunit',
                1 => './ParametersTest.php',
            ],
            'XDEBUG' => 'enabled',
        ];

        $parameters = new Parameters($this->original);

        foreach (array_keys($expected) as $key) {
            $parameters->set($key, $expected[$key]);
        }

        $this->assertSame($expected, $parameters->all());
    }

    public function testOverrideParameters(): void
    {
        /**
         * @var array<string, mixed>
         */
        $expected = [
            'XDEBUG'             => 'enabled',
            'DOCUMENT_ROOT'      => '/www',
            'DISPLAY'            => '0',
            'SCRIPT_NAME'        => '',
            'REQUEST_TIME_FLOAT' => 1741522600.661400,
            'IS_HTTPS'           => false,
            'PHP_NULLABLE_VAR'   => null,
            'OBJECT'             => new stdClass(),
            'argc'               => 2,
            'argv'               => [
                0 => 'bin/phpunit',
                1 => './ParametersTest.php',
            ],
        ];

        $parameters = new Parameters($this->original);

        $parameters->override($expected);

        $this->assertSame($expected, $parameters->all());
    }

    public function testGetUndefinedParametersWithDefaultValueReturn(): void
    {
        $parameters = new Parameters([]);

        $this->assertNull($parameters->get('undefined'));
        $this->assertInstanceOf(stdClass::class, $parameters->get('undefined', new stdClass()));
        $this->assertSame('', $parameters->get('undefined', ''));
        $this->assertSame(1000, $parameters->get('undefined', 1000));
        $this->assertSame(['name' => 'Ivan'], $parameters->get('undefined', ['name' => 'Ivan']));
        $this->assertEqualsWithDelta(10.00, $parameters->get('undefined', 10.00), PHP_FLOAT_EPSILON);
    }

    public function testRemoveKeys(): void
    {
        $parameters = new Parameters($this->original);

        $parameters->remove('argc');
        $parameters->remove('argv');

        unset($this->original['argc'], $this->original['argv']);

        $this->assertSame($this->original, $parameters->all());
    }

    public function testCount(): void
    {
        $parameters = new Parameters($this->original);

        $this->assertCount(count($this->original), $parameters);

        $parameters->remove('DOCUMENT_ROOT');
        $parameters->remove('DISPLAY');

        $this->assertCount(count($this->original) - 2, $parameters);
    }

    public function testGetAll(): void
    {
        $parameters = new Parameters($this->original);

        $this->assertSame($this->original, $parameters->all());
        $this->assertSame(
            [
                'vendor/bin/phpunit',
                './tests/ParametersTest.php',
                '--no-coverage',
            ],
            $parameters->all('argv'),
        );
    }

    public function testAttemptGetAllNonIterableValues(): void
    {
        $parameters = new Parameters($this->original);

        $this->expectException(RuntimeException::class);

        $parameters->all('argc');
    }
}
