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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ControllerGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        foreach (['User.php', 'Blog.php', 'Order.php', 'Pay.php', 'Mixed.php', 'Bogus.php', 'DashboardController.php'] as $file) {
            if (is_file(APPPATH . 'Controllers/' . $file)) {
                unlink(APPPATH . 'Controllers/' . $file);
            }
        }
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private function getContents(string $file): string
    {
        $contents = file_get_contents(APPPATH . 'Controllers/' . $file);
        $this->assertIsString($contents);

        return $contents;
    }

    public function testGenerateController(): void
    {
        command('make:controller user');

        $this->assertSame(
            PHP_EOL . 'File created: ' . clean_path(APPPATH . 'Controllers/User.php') . PHP_EOL,
            $this->getUndecoratedBuffer(),
        );

        $contents = $this->getContents('User.php');
        $this->assertStringContainsString('use App\Controllers\BaseController;', $contents);
        $this->assertStringContainsString('class User extends BaseController', $contents);
    }

    public function testGenerateControllerWithBare(): void
    {
        command('make:controller blog --bare');

        $contents = $this->getContents('Blog.php');
        $this->assertStringContainsString('use CodeIgniter\Controller;', $contents);
        $this->assertStringContainsString('class Blog extends Controller', $contents);
    }

    public function testGenerateControllerWithRestful(): void
    {
        command('make:controller order --restful');

        $contents = $this->getContents('Order.php');
        $this->assertStringContainsString('class Order extends ResourceController', $contents);
        $this->assertStringContainsString('public function show($id = null)', $contents);
    }

    public function testGenerateControllerWithRestfulPresenter(): void
    {
        command('make:controller pay --restful presenter');

        $contents = $this->getContents('Pay.php');
        $this->assertStringContainsString('class Pay extends ResourcePresenter', $contents);
        $this->assertStringContainsString('public function remove($id = null)', $contents);
    }

    public function testBareTakesPrecedenceOverRestful(): void
    {
        command('make:controller mixed --bare --restful');

        $contents = $this->getContents('Mixed.php');
        $this->assertStringContainsString('class Mixed extends Controller', $contents);
        $this->assertStringNotContainsString('public function show', $contents);
    }

    public function testInvalidRestfulTypeIsRejectedWhenNotInteractive(): void
    {
        command('make:controller bogus --restful api --no-interaction');

        $this->assertSame(PHP_EOL . 'Parent class "api" is not valid.' . PHP_EOL, $this->getUndecoratedBuffer());
        $this->assertFileDoesNotExist(APPPATH . 'Controllers/Bogus.php');
    }

    public function testInvalidRestfulTypePromptsWhenInteractive(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['presenter']);
        CLI::setInputOutput($io);

        $command = new ControllerGenerator(new Commands());
        $command->setInteractive(true);

        $this->assertSame(EXIT_SUCCESS, $command->run(['pay'], ['restful' => 'api']));
        $this->assertStringContainsString('Parent class', $io->getOutput());
        $this->assertStringContainsString('class Pay extends ResourcePresenter', $this->getContents('Pay.php'));
    }

    public function testGenerateControllerWithSuffix(): void
    {
        command('make:controller dashboard --suffix');

        $this->assertFileExists(APPPATH . 'Controllers/DashboardController.php');
    }
}
