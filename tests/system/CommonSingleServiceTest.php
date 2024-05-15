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

namespace CodeIgniter;

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockSecurity;
use Config\Security as SecurityConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 */
#[Group('Others')]
final class CommonSingleServiceTest extends CIUnitTestCase
{
    #[DataProvider('provideServiceNames')]
    public function testSingleServiceWithNoParamsSupplied(string $service): void
    {
        Services::injectMock('security', new MockSecurity(new SecurityConfig()));

        $service1 = single_service($service);
        $service2 = single_service($service);

        assert($service1 !== null);

        $this->assertInstanceOf($service1::class, $service2);
        $this->assertNotSame($service1, $service2);
    }

    #[DataProvider('provideServiceNames')]
    public function testSingleServiceWithAtLeastOneParamSupplied(string $service): void
    {
        if ($service === 'commands') {
            $locator = $this->getMockBuilder(FileLocator::class)
                ->setConstructorArgs([Services::autoloader()])
                ->onlyMethods(['listFiles'])
                ->getMock();

            // `Commands::discoverCommand()` is an expensive operation
            $locator->method('listFiles')->with('Commands/')->willReturn([]);
            Services::injectMock('locator', $locator);
        }

        $params = [];
        $method = new ReflectionMethod(Services::class, $service);

        $params[] = $method->getNumberOfParameters() === 1 ? true : $method->getParameters()[0]->getDefaultValue();

        $service1 = single_service($service, ...$params);
        $service2 = single_service($service, ...$params);

        assert($service1 !== null);

        $this->assertInstanceOf($service1::class, $service2);
        $this->assertNotSame($service1, $service2);

        if ($service === 'commands') {
            $this->resetServices();
        }
    }

    public function testSingleServiceWithAllParamsSupplied(): void
    {
        $cache1 = single_service('cache', null, true);
        $cache2 = single_service('cache', null, true);

        assert($cache1 !== null);
        assert($cache2 !== null);

        // Assert that even passing true as last param this will
        // not create a shared instance.
        $this->assertInstanceOf($cache1::class, $cache2);
        $this->assertNotSame($cache1, $cache2);
    }

    public function testSingleServiceWithGibberishGiven(): void
    {
        $this->assertNull(single_service('foo'));
        $this->assertNull(single_service('bar'));
        $this->assertNull(single_service('baz'));
        $this->assertNull(single_service('caches'));
        $this->assertNull(single_service('timers'));
    }

    public static function provideServiceNames(): iterable
    {
        static $services = [];
        static $excl     = [
            'get',
            'set',
            'override',
            '__callStatic',
            'createRequest',
            'serviceExists',
            'reset',
            'resetSingle',
            'injectMock',
            'updateServicesCache',
            'encrypter', // Encrypter needs a starter key
            'session', // Headers already sent
        ];

        if ($services === []) {
            $methods = (new ReflectionClass(Services::class))->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $name = $method->getName();

                if (in_array($name, $excl, true)) {
                    continue;
                }

                $services[$name] = [$name];
            }

            ksort($services);
        }

        yield from $services;
    }
}
