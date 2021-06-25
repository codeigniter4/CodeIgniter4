<?php

namespace CodeIgniter;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 */
final class CommonSingleServiceTest extends CIUnitTestCase
{
    /**
     * @dataProvider serviceNamesProvider
     *
     * @param string $service
     *
     * @return void
     */
    public function testSingleServiceWithNoParamsSupplied(string $service): void
    {
        $service1 = single_service($service);
        $service2 = single_service($service);

        $this->assertSame(get_class($service1), get_class($service2));
        $this->assertNotSame($service1, $service2);
    }

    /**
     * @dataProvider serviceNamesProvider
     *
     * @param string $service
     *
     * @return void
     */
    public function testSingleServiceWithAtLeastOneParamSupplied(string $service): void
    {
        $params = [];
        $method = new ReflectionMethod(Services::class, $service);

        $params[] = $method->getNumberOfParameters() === 1 ? true : $method->getParameters()[0]->getDefaultValue();

        $service1 = single_service($service, ...$params);
        $service2 = single_service($service, ...$params);

        $this->assertSame(get_class($service1), get_class($service2));
        $this->assertNotSame($service1, $service2);
    }

    public function testSingleServiceWithAllParamsSupplied(): void
    {
        $cache1 = single_service('cache', null, true);
        $cache2 = single_service('cache', null, true);

        // Assert that even passing true as last param this will
        // not create a shared instance.
        $this->assertSame(get_class($cache1), get_class($cache2));
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

    public static function serviceNamesProvider(): iterable
    {
        $methods = (new ReflectionClass(Services::class))->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $name = $method->getName();
            $excl = [
                '__callStatic',
                'serviceExists',
                'reset',
                'resetSingle',
                'injectMock',
                'encrypter', // Encrypter needs a starter key
                'session', // Headers already sent
            ];

            if (in_array($name, $excl, true)) {
                continue;
            }

            yield [$name];
        }
    }
}
