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

        $service1 = single_service($service); // @phpstan-ignore codeigniter.unknownServiceMethod
        $service2 = single_service($service); // @phpstan-ignore codeigniter.unknownServiceMethod

        $this->assertNotNull($service1);

        $this->assertInstanceOf($service1::class, $service2);
        $this->assertNotSame($service1, $service2);
    }

    #[DataProvider('provideServiceNames')]
    public function testSingleServiceWithAtLeastOneParamSupplied(string $service): void
    {
        if ($service === 'commands') {
            $locator = $this->getMockBuilder(FileLocator::class)
                ->setConstructorArgs([service('autoloader')])
                ->onlyMethods(['listFiles'])
                ->getMock();

            // `Commands::discoverCommand()` is an expensive operation
            $locator->method('listFiles')->with('Commands/')->willReturn([]);
            Services::injectMock('locator', $locator);
        }

        $params = [];
        $method = new ReflectionMethod(Services::class, $service);

        $params[] = $method->getNumberOfParameters() === 1 ? true : $method->getParameters()[0]->getDefaultValue();

        $service1 = single_service($service, ...$params); // @phpstan-ignore codeigniter.unknownServiceMethod
        $service2 = single_service($service, ...$params); // @phpstan-ignore codeigniter.unknownServiceMethod

        $this->assertNotNull($service1);

        $this->assertInstanceOf($service1::class, $service2);
        $this->assertNotSame($service1, $service2);

        if ($service === 'commands') {
            $this->resetServices();
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
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
            'resetServicesCache',
            'resetForWorkerMode',
            'injectMock',
            'has',
            'encrypter', // Encrypter needs a starter key
            'session', // Headers already sent
            'reconnectCacheForWorkerMode',
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

    public function testSingleServiceWithAllParamsSupplied(): void
    {
        $cache1 = single_service('cache', null, true);
        $cache2 = single_service('cache', null, true);

        $this->assertNotNull($cache1);
        $this->assertNotNull($cache2);

        // Assert that even passing true as last param this will
        // not create a shared instance.
        $this->assertInstanceOf($cache1::class, $cache2);
        $this->assertNotSame($cache1, $cache2);
    }

    public function testSingleServiceWithGibberishGiven(): void
    {
        $this->assertNull(single_service('foo')); // @phpstan-ignore codeigniter.unknownServiceMethod
        $this->assertNull(single_service('bar')); // @phpstan-ignore codeigniter.unknownServiceMethod
        $this->assertNull(single_service('baz')); // @phpstan-ignore codeigniter.unknownServiceMethod
        $this->assertNull(single_service('caches')); // @phpstan-ignore codeigniter.unknownServiceMethod
        $this->assertNull(single_service('timers')); // @phpstan-ignore codeigniter.unknownServiceMethod
    }
}
