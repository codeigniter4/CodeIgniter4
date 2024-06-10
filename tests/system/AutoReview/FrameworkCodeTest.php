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

use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use SplFileInfo;

/**
 * @internal
 */
#[Group('AutoReview')]
final class FrameworkCodeTest extends TestCase
{
    /**
     * Cache of discovered test class names.
     */
    private static array $testClasses = [];

    private static array $recognizedGroupAttributeNames = [
        'AutoReview',
        'CacheLive',
        'DatabaseLive',
        'Others',
        'SeparateProcess',
    ];

    /**
     * @param class-string $class
     */
    #[DataProvider('provideEachTestClassHasCorrectGroupAttributeName')]
    public function testEachTestClassHasCorrectGroupAttributeName(string $class): void
    {
        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract()) {
            $this->addToAssertionCount(1);

            return;
        }

        $attributes = $reflection->getAttributes(Group::class);
        $this->assertNotEmpty($attributes, sprintf('[%s] Test class is missing a #[Group] attribute.', $class));

        $unrecognizedGroups = array_diff(
            array_map(static function (ReflectionAttribute $attribute): string {
                $groupAttribute = $attribute->newInstance();
                assert($groupAttribute instanceof Group);

                return $groupAttribute->name();
            }, $attributes),
            self::$recognizedGroupAttributeNames
        );
        $this->assertEmpty($unrecognizedGroups, sprintf(
            "[%s] Unexpected #[Group] attribute%s:\n%s\nExpected group names to be in \"%s\".",
            $class,
            count($unrecognizedGroups) > 1 ? 's' : '',
            implode("\n", array_map(
                static fn (string $group): string => sprintf('  * #[Group(\'%s\')]', $group),
                $unrecognizedGroups
            )),
            implode(', ', self::$recognizedGroupAttributeNames)
        ));
    }

    public static function provideEachTestClassHasCorrectGroupAttributeName(): iterable
    {
        foreach (self::getTestClasses() as $class) {
            yield $class => [$class];
        }
    }

    private static function getTestClasses(): array
    {
        if (self::$testClasses !== []) {
            return self::$testClasses;
        }

        helper('filesystem');

        $directory = set_realpath(dirname(__DIR__), true);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $testClasses = array_map(
            static function (SplFileInfo $file) use ($directory): string {
                $relativePath = substr_replace(
                    $file->getPathname(),
                    '',
                    0,
                    strlen($directory)
                );
                $relativePath = substr_replace(
                    $relativePath,
                    '',
                    strlen($relativePath) - strlen(DIRECTORY_SEPARATOR . $file->getBasename())
                );

                return sprintf(
                    'CodeIgniter\\%s%s%s',
                    strtr($relativePath, DIRECTORY_SEPARATOR, '\\'),
                    $relativePath === '' ? '' : '\\',
                    $file->getBasename('.' . $file->getExtension())
                );
            },
            array_filter(
                iterator_to_array($iterator, false),
                static fn (SplFileInfo $file): bool => $file->isFile()
                    && ! str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR)
                    && ! str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR)
            )
        );

        $testClasses = array_filter(
            $testClasses,
            static fn (string $class) => is_subclass_of($class, TestCase::class)
        );

        sort($testClasses);

        self::$testClasses = $testClasses;

        return $testClasses;
    }
}
