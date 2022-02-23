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

namespace CodeIgniter\AutoReview;

use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 * @group auto-review
 */
final class ComposerJsonTest extends TestCase
{
    public function testFrameworkRequireIsTheSameWithDevRequire(): void
    {
        $devComposer       = $this->getComposerJson(dirname(__DIR__, 2) . '/composer.json');
        $frameworkComposer = $this->getComposerJson(dirname(__DIR__, 2) . '/admin/framework/composer.json');

        $this->assertSame(
            $devComposer['require'],
            $frameworkComposer['require'],
            'The framework\'s "require" section is not updated with the main composer.json.'
        );
    }

    public function testFrameworkRequireDevIsTheSameWithDevRequireDev(): void
    {
        $devComposer       = $this->getComposerJson(dirname(__DIR__, 2) . '/composer.json');
        $frameworkComposer = $this->getComposerJson(dirname(__DIR__, 2) . '/admin/framework/composer.json');

        $devRequireDev = $devComposer['require-dev'];
        $fwRequireDev  = $frameworkComposer['require-dev'];

        foreach ($devRequireDev as $dependency => $expectedVersion) {
            if (! isset($fwRequireDev[$dependency])) {
                $this->addToAssertionCount(1);

                continue;
            }

            $this->assertSame($expectedVersion, $fwRequireDev[$dependency], sprintf(
                'Framework\'s "%s" dev dependency is expected to have version constraint of "%s", found "%s" instead.' .
                "\nPlease update the version constraint at %s.",
                $dependency,
                $expectedVersion,
                $fwRequireDev[$dependency],
                clean_path(dirname(__DIR__, 2) . '/admin/framework/composer.json')
            ));
        }
    }

    private function getComposerJson(string $path): array
    {
        try {
            return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail(sprintf(
                'The composer.json at "%s" is not readable or does not exist. Error was "%s".',
                clean_path($path),
                $e->getMessage()
            ));
        }
    }
}
