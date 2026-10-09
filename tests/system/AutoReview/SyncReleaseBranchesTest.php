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

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * The successful path switches branches and merges, so only the argument
 * validation is exercised here.
 *
 * @internal
 */
#[CoversNothing]
#[Group('AutoReview')]
final class SyncReleaseBranchesTest extends TestCase
{
    #[DataProvider('provideUsageErrors')]
    public function testUsageErrors(string $arguments): void
    {
        exec(sprintf('php ./admin/sync-release-branches.php %s 2>&1', $arguments), $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $output));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideUsageErrors(): iterable
    {
        yield 'no arguments' => [''];

        yield 'missing source' => ['4.8'];

        yield 'invalid branch' => ['release-4.8.0 develop'];

        yield 'invalid source' => ['4.8 upstream/develop'];

        yield 'same branch and source' => ['develop develop'];

        yield 'unknown option' => ['4.8 develop --force'];
    }
}
