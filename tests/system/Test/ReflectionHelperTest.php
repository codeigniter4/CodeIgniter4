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

namespace CodeIgniter\Test;

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Test\TestForReflectionHelper;

/**
 * @internal
 */
#[Group('Others')]
final class ReflectionHelperTest extends CIUnitTestCase
{
    public function testGetPrivatePropertyWithObject(): void
    {
        $obj    = new TestForReflectionHelper();
        $actual = $this->getPrivateProperty($obj, 'private');
        $this->assertSame('secret', $actual);
    }

    public function testGetPrivatePropertyWithObjectStaticCall(): void
    {
        $obj    = new TestForReflectionHelper();
        $actual = CIUnitTestCase::getPrivateProperty($obj, 'private');
        $this->assertSame('secret', $actual);
    }

    public function testGetPrivatePropertyWithStatic(): void
    {
        $actual = $this->getPrivateProperty(
            TestForReflectionHelper::class,
            'static_private',
        );
        $this->assertSame('xyz', $actual);
    }

    public function testSetPrivatePropertyWithObject(): void
    {
        $obj = new TestForReflectionHelper();
        $this->setPrivateProperty(
            $obj,
            'private',
            'open',
        );
        $this->assertSame('open', $obj->getPrivate());
    }

    public function testSetPrivatePropertyWithStatic(): void
    {
        $this->setPrivateProperty(
            TestForReflectionHelper::class,
            'static_private',
            'abc',
        );
        $this->assertSame(
            'abc',
            TestForReflectionHelper::getStaticPrivate(),
        );
    }

    public function testGetPrivateMethodInvokerWithObject(): void
    {
        $obj    = new TestForReflectionHelper();
        $method = self::getPrivateMethodInvoker(
            $obj,
            'privateMethod',
        );
        $this->assertSame(
            'private param1param2',
            $method('param1', 'param2'),
        );
    }

    public function testGetPrivateMethodInvokerWithStatic(): void
    {
        $method = self::getPrivateMethodInvoker(
            TestForReflectionHelper::class,
            'privateStaticMethod',
        );
        $this->assertSame(
            'private_static param1param2',
            $method('param1', 'param2'),
        );
    }

    public function testGetPrivatePropertyWithAnonymousObject(): void
    {
        $anonClassObject = new class () extends TestForReflectionHelper {
            private string $hideMe = 'active';

            public function getHideMe(): string
            {
                return $this->hideMe;
            }
        };

        $hideMe  = $this->getPrivateProperty($anonClassObject, 'hideMe');
        $private = $this->getPrivateProperty($anonClassObject, 'private');

        $this->assertSame('active', $hideMe);
        $this->assertSame($anonClassObject->getPrivate(), $private);
    }

    public function testSetPrivatePropertyWithAnonymousObject(): void
    {
        $anonClassObject = new class () extends TestForReflectionHelper {
            private string $hideMe = 'active';

            public function getHideMe(): string
            {
                return $this->hideMe;
            }
        };

        $this->setPrivateProperty($anonClassObject, 'hideMe', 'inactive');
        $this->setPrivateProperty($anonClassObject, 'private', 'new secret');

        $hideMe  = $this->getPrivateProperty($anonClassObject, 'hideMe');
        $private = $this->getPrivateProperty($anonClassObject, 'private');

        $this->assertSame('inactive', $hideMe);
        $this->assertSame('new secret', $private);
        $this->assertSame('new secret', $anonClassObject->getPrivate());
    }
}
