<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\PHPStan\Type;

use PHPStan\Testing\TypeInferenceTestCase;

/**
 * @internal
 *
 * @group AutoReview
 */
final class ExtensionTest extends TypeInferenceTestCase
{
    /**
     * @dataProvider provideFileAssertsCases
     *
     * @param mixed ...$args
     */
    public function testFileAsserts(string $assertType, string $file, ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    public static function provideFileAssertsCases(): iterable
    {
        yield from self::gatherAssertTypes(SUPPORTPATH . 'PHPStan/Type/config.php');

        yield from self::gatherAssertTypes(SUPPORTPATH . 'PHPStan/Type/model.php');
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../../../extension.neon.dist',
        ];
    }
}
