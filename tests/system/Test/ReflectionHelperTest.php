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

/**
 * @internal
 *
 * @group Others
 */
final class ReflectionHelperTest extends CIUnitTestCase
{
    public function testGetPrivatePropertyWithObject(): void
    {
        $obj    = new __TestForReflectionHelper();
        $actual = $this->getPrivateProperty($obj, 'private');
        $this->assertSame('secret', $actual);
    }

    public function testGetPrivatePropertyWithObjectStaticCall(): void
    {
        $obj    = new __TestForReflectionHelper();
        $actual = CIUnitTestCase::getPrivateProperty($obj, 'private');
        $this->assertSame('secret', $actual);
    }

    public function testGetPrivatePropertyWithStatic(): void
    {
        $actual = $this->getPrivateProperty(
            __TestForReflectionHelper::class,
            'static_private'
        );
        $this->assertSame('xyz', $actual);
    }

    public function testSetPrivatePropertyWithObject(): void
    {
        $obj = new __TestForReflectionHelper();
        $this->setPrivateProperty(
            $obj,
            'private',
            'open'
        );
        $this->assertSame('open', $obj->getPrivate());
    }

    public function testSetPrivatePropertyWithStatic(): void
    {
        $this->setPrivateProperty(
            __TestForReflectionHelper::class,
            'static_private',
            'abc'
        );
        $this->assertSame(
            'abc',
            __TestForReflectionHelper::getStaticPrivate()
        );
    }

    public function testGetPrivateMethodInvokerWithObject(): void
    {
        $obj    = new __TestForReflectionHelper();
        $method = $this->getPrivateMethodInvoker(
            $obj,
            'privateMethod'
        );
        $this->assertSame(
            'private param1param2',
            $method('param1', 'param2')
        );
    }

    public function testGetPrivateMethodInvokerWithStatic(): void
    {
        $method = $this->getPrivateMethodInvoker(
            __TestForReflectionHelper::class,
            'privateStaticMethod'
        );
        $this->assertSame(
            'private_static param1param2',
            $method('param1', 'param2')
        );
    }
}

class __TestForReflectionHelper
{
    private string $private               = 'secret';
    private static string $static_private = 'xyz';

    public function getPrivate()
    {
        return $this->private;
    }

    public static function getStaticPrivate()
    {
        return self::$static_private;
    }

    private function privateMethod($param1, $param2)
    {
        return 'private ' . $param1 . $param2;
    }

    private static function privateStaticMethod($param1, $param2)
    {
        return 'private_static ' . $param1 . $param2;
    }
}
