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

namespace CodeIgniter\Debug\Inspection;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(CodeSnippet::class)]
#[Group('Others')]
final class CodeSnippetTest extends CIUnitTestCase
{
    public function testLimitsTheSnippetAndRetainsMultilineTokenContext(): void
    {
        $source = "<?php\r\n/*\r\n" . str_repeat("<tag>\r\n", 18) . "*/\r\nreturn 'done';";

        $this->withSource($source, function (string $file): void {
            $lines = CodeSnippet::get($file, 12);

            $this->assertSame(range(5, 19), array_keys($lines));
            $this->assertSame('<span class="php-comment">&lt;tag&gt;</span>', $lines[5]);
            $this->assertSame([], CodeSnippet::get($file, 100));
        });
    }

    public function testPlainExcerptKeepsSourceTextAndLimitsContext(): void
    {
        $source = "<?php\r\n" . str_repeat("// <tag>\r\n", 9);

        $this->withSource($source, function (string $file): void {
            $lines = CodeSnippet::get($file, 6, highlight: false);

            $this->assertSame(array_fill(3, 7, '// <tag>'), $lines);
            $this->assertSame([], CodeSnippet::get($file, 100, highlight: false));
        });
    }

    public function testTruncatesLongLines(): void
    {
        $this->withSource("<?php\n//" . str_repeat('x', 2_000), function (string $file): void {
            $lines = CodeSnippet::get($file, 2);

            $this->assertSame('//' . str_repeat('x', 998) . '…', strip_tags($lines[2]));
        });
    }

    public function testTruncatesAtUtf8BoundariesAcrossTokens(): void
    {
        $prefix = 'echo "' . str_repeat('x', 993);

        $this->withSource(sprintf("<?php\n%sé\"; echo 1;\nreturn 2;", $prefix), function (string $file) use ($prefix): void {
            $lines = CodeSnippet::get($file, 2);

            $this->assertSame($prefix . '…', html_entity_decode(strip_tags($lines[2]), ENT_QUOTES, 'UTF-8'));
            $this->assertSame('return 2;', strip_tags($lines[3]));
        });
    }

    public function testSkipsOversizedFilesAndInvalidLocations(): void
    {
        $this->withSource(str_repeat('x', 1_048_577), function (string $file): void {
            $this->assertSame([], CodeSnippet::get($file, 1));
        });

        $this->assertSame([], CodeSnippet::get(__FILE__, 0));
        $this->assertSame([], CodeSnippet::get(__FILE__, null));
        $this->assertSame([], CodeSnippet::get(null, 1));
        $this->assertSame([], CodeSnippet::get(__FILE__ . '.missing.php', 1));
    }

    /**
     * @param callable(string): void $test
     */
    private function withSource(string $source, callable $test): void
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ci-snippet-' . bin2hex(random_bytes(8)) . '.php';

        try {
            file_put_contents($file, $source);
            $test($file);
        } finally {
            unlink($file);
        }
    }
}
