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

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class EnvironmentDetectorTest extends CIUnitTestCase
{
    public function testDefaultsToEnvironmentConstant(): void
    {
        $detector = new EnvironmentDetector();

        $this->assertSame(ENVIRONMENT, $detector->get());
    }

    public function testExplicitEnvironmentOverridesConstant(): void
    {
        $detector = new EnvironmentDetector('production');

        $this->assertSame('production', $detector->get());
    }

    public function testTrimsSurroundingWhitespace(): void
    {
        $detector = new EnvironmentDetector("  production\n");

        $this->assertSame('production', $detector->get());
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Environment cannot be an empty string.');

        new EnvironmentDetector(''); // @phpstan-ignore argument.type
    }

    public function testRejectsWhitespaceOnlyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Environment cannot be an empty string.');

        new EnvironmentDetector("  \t\n");
    }

    public function testIsMatchesSingleEnvironment(): void
    {
        $detector = new EnvironmentDetector('staging');

        $this->assertTrue($detector->is('staging'));
        $this->assertFalse($detector->is('production'));
    }

    public function testIsMatchesAnyOfSeveralEnvironments(): void
    {
        $detector = new EnvironmentDetector('staging');

        $this->assertTrue($detector->is('production', 'staging', 'development'));
        $this->assertFalse($detector->is('production', 'development', 'testing'));
    }

    public function testIsReturnsFalseWhenNoEnvironmentsGiven(): void
    {
        $detector = new EnvironmentDetector('production');

        $this->assertFalse($detector->is());
    }

    public function testIsIsCaseSensitive(): void
    {
        $detector = new EnvironmentDetector('production');

        $this->assertFalse($detector->is('Production'));
        $this->assertFalse($detector->is('PRODUCTION'));
    }

    /**
     * @param non-empty-string $environment
     */
    #[DataProvider('provideBuiltInEnvironmentHelpers')]
    public function testBuiltInEnvironmentHelpers(string $environment, bool $isProduction, bool $isDevelopment, bool $isTesting): void
    {
        $detector = new EnvironmentDetector($environment);

        $this->assertSame($isProduction, $detector->isProduction());
        $this->assertSame($isDevelopment, $detector->isDevelopment());
        $this->assertSame($isTesting, $detector->isTesting());
    }

    /**
     * @return iterable<string, array{string, bool, bool, bool}>
     */
    public static function provideBuiltInEnvironmentHelpers(): iterable
    {
        yield 'production' => ['production', true, false, false];

        yield 'development' => ['development', false, true, false];

        yield 'testing' => ['testing', false, false, true];

        yield 'custom' => ['staging', false, false, false];
    }

    public function testResolvesAsSharedService(): void
    {
        $first  = service('environment');
        $second = service('environment');

        $this->assertInstanceOf(EnvironmentDetector::class, $first);
        $this->assertSame($first, $second);
        $this->assertSame(ENVIRONMENT, $first->get());
    }
}
