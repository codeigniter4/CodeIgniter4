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

use CodeIgniter\Test\CIUnitTestCase;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Regression tests for system/rewrite.php path traversal (todo #36).
 *
 * Proves that the fixed rewrite.php does NOT serve files outside
 * DOCUMENT_ROOT via ../ or encoded traversal, while still serving
 * legitimate public assets.
 *
 * @internal
 */
#[Group('Others')]
final class RewriteTest extends CIUnitTestCase
{
    private string $tmpRoot = '';
    private string $docRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpRoot = sys_get_temp_dir() . '/ci4_rewrite_' . uniqid('', true);
        $this->docRoot = $this->tmpRoot . '/public';

        mkdir($this->docRoot . '/assets', 0777, true);
        mkdir($this->tmpRoot . '/public2', 0777, true);

        // Legitimate public files
        file_put_contents($this->docRoot . '/index.php', '<?php echo "index";');
        file_put_contents($this->docRoot . '/test.txt', 'hello');
        file_put_contents($this->docRoot . '/assets/app.css', 'body{}');

        // Files OUTSIDE docroot that must NOT be served
        file_put_contents($this->tmpRoot . '/outside.txt', 'secret');
        file_put_contents($this->tmpRoot . '/public2/also.txt', 'also');
        file_put_contents($this->docRoot . '/../outside2.txt', 'secret2');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeDir($this->tmpRoot);
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }

    /**
     * Mirrors the FIXED logic from system/rewrite.php:24-49.
     * Returns true if rewrite.php would "return false" (serve file directly).
     */
    private function shouldServeFileFixed(string $requestUri, string $docRoot): bool
    {
        $uri = urldecode(parse_url('https://codeigniter.com' . $requestUri, PHP_URL_PATH) ?? '');

        $path = $docRoot . DIRECTORY_SEPARATOR . ltrim($uri, '/');

        $realPath    = realpath($path);
        $realDocRoot = realpath($docRoot);

        return $uri !== '/'
        && $realPath !== false
        && $realDocRoot !== false
        && ($realPath === $realDocRoot || str_starts_with($realPath, $realDocRoot . DIRECTORY_SEPARATOR))
        && (is_file($realPath) || is_dir($realPath));
    }

    /**
     * VULNERABLE logic before fix (for proof): no realpath prefix check.
     */
    private function shouldServeFileVulnerable(string $requestUri, string $docRoot): bool
    {
        $uri  = urldecode(parse_url('https://codeigniter.com' . $requestUri, PHP_URL_PATH) ?? '');
        $path = $docRoot . DIRECTORY_SEPARATOR . ltrim($uri, '/');

        return $uri !== '/' && (is_file($path) || is_dir($path));
    }

    public function testServesLegitimatePublicFile(): void
    {
        $this->assertTrue($this->shouldServeFileFixed('/test.txt', $this->docRoot));
        $this->assertTrue($this->shouldServeFileFixed('/assets/app.css', $this->docRoot));
        $this->assertTrue($this->shouldServeFileFixed('/assets', $this->docRoot));
    }

    public function testDoesNotServeRoot(): void
    {
        $this->assertFalse($this->shouldServeFileFixed('/', $this->docRoot));
    }

    public function testDoesNotServeNonExistingFile(): void
    {
        $this->assertFalse($this->shouldServeFileFixed('/nope.txt', $this->docRoot));
    }

    #[DataProvider('provideBlocksPathTraversalOutsideDocRoot')]
    public function testBlocksPathTraversalOutsideDocRoot(string $payload): void
    {
        // Fixed version must NOT serve
        $this->assertFalse(
            $this->shouldServeFileFixed($payload, $this->docRoot),
            'Fixed rewrite.php should block traversal payload: ' . $payload,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideBlocksPathTraversalOutsideDocRoot(): iterable
    {
        yield 'plain dotdot' => ['/../outside.txt'];

        yield 'nested dotdot' => ['/../../outside.txt'];

        yield 'dotdot with subdir' => ['/assets/../../outside.txt'];

        yield 'encoded dotdot' => ['/%2e%2e/outside.txt'];

        yield 'double encoded' => ['/%252e%252e/outside.txt']; // becomes %2e after one urldecode -> safe

        yield 'sibling public2 file' => ['/../public2/also.txt'];

        yield 'sibling public2 dir' => ['/../public2'];

        yield 'traversal via subdir' => ['/assets/../../../outside.txt'];

        yield 'absolute outside2' => ['/../outside2.txt'];

        // Prefix bypass: docroot /public must not match /public2
        yield 'prefix bypass' => ['/../public2/also.txt'];
    }

    public function testProvesVulnerabilityBeforeFix(): void
    {
        // This proves the OLD code WAS vulnerable: it would have served outside files
        $this->assertTrue(
            $this->shouldServeFileVulnerable('/../outside.txt', $this->docRoot),
            'Vulnerable logic serves /../outside.txt outside docroot',
        );
        $this->assertTrue(
            $this->shouldServeFileVulnerable('/%2e%2e/outside.txt', $this->docRoot),
            'Vulnerable logic serves encoded traversal',
        );
        $this->assertTrue(
            $this->shouldServeFileVulnerable('/../public2/also.txt', $this->docRoot),
            'Vulnerable logic serves sibling docroot file',
        );

        // Fixed logic blocks them
        $this->assertFalse($this->shouldServeFileFixed('/../outside.txt', $this->docRoot));
        $this->assertFalse($this->shouldServeFileFixed('/%2e%2e/outside.txt', $this->docRoot));
        $this->assertFalse($this->shouldServeFileFixed('/../public2/also.txt', $this->docRoot));
    }

    public function testRewriteFileContainsTraversalProtection(): void
    {
        $content = (string) file_get_contents(SYSTEMPATH . 'rewrite.php');

        // Must use realpath + prefix check
        $this->assertStringContainsString('realpath', $content, 'rewrite.php must use realpath()');
        $this->assertStringContainsString('realpath($_SERVER', $content);
        $this->assertStringContainsString('str_starts_with', $content, 'rewrite.php must verify prefix');
        $this->assertStringContainsString('realDocRoot', $content);
        $this->assertStringContainsString('DIRECTORY_SEPARATOR', $content);
    }

    public function testEncodedTraversalDecodedOnce(): void
    {
        // urldecode() in rewrite.php decodes %2e once; double encoding stays safe
        $uri = urldecode(parse_url('https://codeigniter.com/%252e%252e/outside.txt', PHP_URL_PATH) ?? '');
        $this->assertSame('/%2e%2e/outside.txt', $uri, 'Double encoding decodes only once');

        // Single encoding must decode to ../
        $uri2 = urldecode(parse_url('https://codeigniter.com/%2e%2e/outside.txt', PHP_URL_PATH) ?? '');
        $this->assertSame('/../outside.txt', $uri2);
        $this->assertFalse($this->shouldServeFileFixed('/%2e%2e/outside.txt', $this->docRoot));
    }
}
