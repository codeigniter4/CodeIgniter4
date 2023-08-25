<?php

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
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

/**
 * @internal
 *
 * @group AutoReview
 */
final class FrameworkCodeTest extends TestCase
{
    /**
     * Cache of discovered test class names.
     */
    private static array $testClasses = [];

    private static array $recognizedGroupAnnotations = [
        'AutoReview',
        'CacheLive',
        'DatabaseLive',
        'Others',
        'SeparateProcess',
    ];

    /**
     * @dataProvider provideEachTestClassHasCorrectGroupAnnotation
     *
     * @phpstan-param class-string $class
     */
    public function testEachTestClassHasCorrectGroupAnnotation(string $class): void
    {
        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract()) {
            $this->addToAssertionCount(1);

            return;
        }

        $docComment = (string) $reflection->getDocComment();
        $this->assertNotEmpty($docComment, sprintf('[%s] Test class is missing a class-level PHPDoc.', $class));

        preg_match_all('/@group (\S+)/', $docComment, $matches);
        array_shift($matches);
        $this->assertNotEmpty($matches[0], sprintf('[%s] Test class is missing a @group annotation.', $class));

        $unrecognizedGroups = array_diff($matches[0], self::$recognizedGroupAnnotations);
        $this->assertEmpty($unrecognizedGroups, sprintf(
            "[%s] Unexpected @group annotation%s:\n%s\nExpected annotations to be in \"%s\".",
            $class,
            count($unrecognizedGroups) > 1 ? 's' : '',
            implode("\n", array_map(
                static fn (string $group): string => sprintf('  * @group %s', $group),
                $unrecognizedGroups
            )),
            implode(', ', self::$recognizedGroupAnnotations)
        ));
    }

    public function provideEachTestClassHasCorrectGroupAnnotation(): iterable
    {
        foreach ($this->getTestClasses() as $class) {
            yield $class => [$class];
        }
    }

    private function getTestClasses(): array
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
                    && strpos($file->getPathname(), DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR) === false
                    && strpos($file->getPathname(), DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR) === false
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
