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

use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @group AutoReview
 */
final class ComposerJsonTest extends TestCase
{
    private array $devComposer;
    private array $frameworkComposer;
    private array $starterComposer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->devComposer       = $this->getComposerJson(dirname(__DIR__, 3) . '/composer.json');
        $this->frameworkComposer = $this->getComposerJson(dirname(__DIR__, 3) . '/admin/framework/composer.json');
        $this->starterComposer   = $this->getComposerJson(dirname(__DIR__, 3) . '/admin/starter/composer.json');
    }

    public function testFrameworkRequireIsTheSameWithDevRequire(): void
    {
        $this->checkSection('require', 'framework');
    }

    public function testFrameworkRequireDevIsTheSameWithDevRequireDev(): void
    {
        $devRequireDev = $this->devComposer['require-dev'];
        $fwRequireDev  = $this->frameworkComposer['require-dev'];

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

    public function testFrameworkSuggestIsTheSameWithDevSuggest(): void
    {
        $this->checkSection('suggest', 'framework');
    }

    public function testFrameworkConfigIsTheSameWithDevSuggest(): void
    {
        $this->checkConfig(
            $this->devComposer['config'],
            $this->frameworkComposer['config'],
            'framework'
        );
    }

    public function testStarterConfigIsTheSameWithDevSuggest(): void
    {
        $this->checkConfig(
            $this->devComposer['config'],
            $this->starterComposer['config'],
            'starter'
        );
    }

    private function checkSection(string $section, string $component): void
    {
        switch (strtolower($component)) {
            case 'framework':
                $sectionContent = $this->frameworkComposer[$section] ?? null;
                break;

            case 'starter':
                $sectionContent = $this->starterComposer[$section] ?? null;
                break;

            default:
                throw new InvalidArgumentException(sprintf('Unknown component: %s.', $component));
        }

        $this->assertSame(
            $this->devComposer[$section],
            $sectionContent,
            sprintf('The %s\'s "%s" section is not updated with the main composer.json', strtolower($component), $section)
        );
    }

    private function checkConfig(array $fromMain, array $fromComponent, string $component): void
    {
        foreach ($fromMain as $key => $expectedValue) {
            if (! isset($fromComponent[$key])) {
                $this->addToAssertionCount(1);

                continue;
            }

            $actualValue = $fromComponent[$key];

            $this->assertSame($expectedValue, $actualValue, sprintf(
                '%s\'s value for config property "%s" is not same with the main composer.json\'s config.',
                ucfirst($component),
                $key
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
