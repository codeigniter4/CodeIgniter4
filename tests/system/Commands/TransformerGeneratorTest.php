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

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class TransformerGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        $result = str_replace(["\033[0;33m", "\033[0;32m", "\033[0m", "\n"], '', $this->getStreamFilterBuffer());
        preg_match('/APPPATH(\/[^\s"]+\.php)/', $result, $matches);
        $file = isset($matches[0]) ? str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $matches[0]) : '';

        if (is_file($file)) {
            unlink($file);
        }
    }

    protected function getFileContents(string $filepath): string
    {
        if (! is_file($filepath)) {
            return '';
        }

        $contents = file_get_contents($filepath);

        return $contents !== false ? $contents : '';
    }

    public function testGenerateTransformer(): void
    {
        command('make:transformer user');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Transformers/User.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('extends BaseTransformer', $contents);
        $this->assertStringContainsString('namespace App\Transformers', $contents);
        $this->assertStringContainsString('public function toArray(mixed $resource): array', $contents);
    }

    public function testGenerateTransformerWithSubdirectory(): void
    {
        command('make:transformer api/v1/product');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Transformers/Api/V1/Product.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('namespace App\Transformers\Api\V1', $contents);
        $this->assertStringContainsString('class Product extends BaseTransformer', $contents);
    }

    public function testGenerateTransformerWithOptionSuffix(): void
    {
        command('make:transformer order -suffix');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Transformers/OrderTransformer.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('class OrderTransformer extends BaseTransformer', $contents);
    }

    public function testGenerateTransformerWithOptionForce(): void
    {
        // Create the file first
        command('make:transformer customer');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Transformers/Customer.php';
        $this->assertFileExists($file);

        // Try to overwrite without force
        $this->resetStreamFilterBuffer();
        command('make:transformer customer');
        $this->assertStringContainsString('File exists: ', $this->getStreamFilterBuffer());

        // Now overwrite with force
        $this->resetStreamFilterBuffer();
        command('make:transformer customer -force');
        $this->assertStringContainsString('File overwritten: ', $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
    }
}
