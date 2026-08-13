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

namespace CodeIgniter\Commands\Utilities;

use Closure;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class OptimizeTest extends CIUnitTestCase
{
    use ReflectionHelper;
    use StreamFilterTrait;

    private string $file = WRITEPATH . 'cache/OptimizeTest_config';

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * @return Closure(string): void
     */
    private function getRemoveFile(): Closure
    {
        return self::getPrivateMethodInvoker(new Optimize(service('commands')), 'removeFile');
    }

    public function testRemoveFileDeletesTheFile(): void
    {
        file_put_contents($this->file, '<?php');

        ($this->getRemoveFile())($this->file);

        $this->assertFileDoesNotExist($this->file);
        $this->assertStringContainsString('Removed', $this->getStreamFilterBuffer());
    }

    public function testRemoveFileDoesNothingWhenFileIsAbsent(): void
    {
        $this->assertFileDoesNotExist($this->file);

        ($this->getRemoveFile())($this->file);

        $this->assertSame('', $this->getStreamFilterBuffer());
    }
}
