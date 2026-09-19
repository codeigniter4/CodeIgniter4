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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ScaffoldGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private const TIMESTAMP = '\d{4}-\d{2}-\d{2}-\d{6}';

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        foreach (['People', 'User', 'Order', 'Fixer', 'Product'] as $name) {
            foreach (['Controllers', 'Models', 'Entities', 'Database/Seeds', 'Database/Migrations'] as $dir) {
                foreach (glob(sprintf('%s%s/*%s*.php', APPPATH, $dir, $name)) as $file) {
                    unlink($file);
                }
            }
        }

        if (is_dir(APPPATH . 'Entities')) {
            rmdir(APPPATH . 'Entities');
        }
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private function getContents(string $file): string
    {
        $contents = file_get_contents(APPPATH . $file);
        $this->assertIsString($contents);

        return $contents;
    }

    public function testCreateComponentProducesManyFiles(): void
    {
        command('make:scaffold people');

        $this->assertMatchesRegularExpression(
            sprintf(
                "#^\nFile created: APPPATH/Controllers/People\\.php\nFile created: APPPATH/Models/People\\.php\n"
                . "File created: APPPATH/Database/Migrations/%s_People\\.php\nFile created: APPPATH/Database/Seeds/People\\.php\n$#",
                self::TIMESTAMP,
            ),
            $this->getUndecoratedBuffer(),
        );
    }

    public function testCreateComponentWithManyOptions(): void
    {
        command('make:scaffold user --restful --return entity --table members --dbgroup tests');

        $this->assertStringContainsString('class User extends ResourceController', $this->getContents('Controllers/User.php'));

        $model = $this->getContents('Models/User.php');
        $this->assertStringContainsString("protected \$DBGroup                = 'tests';", $model);
        $this->assertStringContainsString("protected \$table                  = 'members';", $model);
        $this->assertStringContainsString('protected $returnType             = \App\Entities\User::class;', $model);

        $this->assertFileExists(APPPATH . 'Entities/User.php');
        $this->assertFileExists(APPPATH . 'Database/Seeds/User.php');
        $this->assertCount(1, glob(APPPATH . 'Database/Migrations/*_User.php'));
    }

    public function testCreateComponentWithRestfulPresenter(): void
    {
        command('make:scaffold user --restful presenter');

        $this->assertStringContainsString('class User extends ResourcePresenter', $this->getContents('Controllers/User.php'));
    }

    public function testCreateComponentWithOptionSuffix(): void
    {
        command('make:scaffold order -s');

        $this->assertFileExists(APPPATH . 'Controllers/OrderController.php');
        $this->assertFileExists(APPPATH . 'Models/OrderModel.php');
        $this->assertFileExists(APPPATH . 'Database/Seeds/OrderSeeder.php');
        $this->assertCount(1, glob(APPPATH . 'Database/Migrations/*_OrderMigration.php'));
    }

    public function testCreateComponentWithOptionForce(): void
    {
        command('make:controller fixer');
        $this->assertStringContainsString('class Fixer extends BaseController', $this->getContents('Controllers/Fixer.php'));
        $this->resetStreamFilterBuffer();

        command('make:scaffold fixer -b -f');

        $this->assertMatchesRegularExpression(
            sprintf(
                "#^File overwritten: \"APPPATH/Controllers/Fixer\\.php\"\nFile created: APPPATH/Models/Fixer\\.php\n"
                . "File created: APPPATH/Database/Migrations/%s_Fixer\\.php\nFile created: APPPATH/Database/Seeds/Fixer\\.php\n$#",
                self::TIMESTAMP,
            ),
            $this->getUndecoratedBuffer(),
        );
        $this->assertStringContainsString('class Fixer extends Controller', $this->getContents('Controllers/Fixer.php'));
    }

    public function testExistingFilesFailWithoutForce(): void
    {
        command('make:scaffold people');
        $this->resetStreamFilterBuffer();

        command('make:scaffold people');

        $this->assertMatchesRegularExpression(
            sprintf(
                "#^File exists: \"APPPATH/Controllers/People\\.php\"\nFile exists: \"APPPATH/Models/People\\.php\"\n"
                . "File (?:created: |exists: \")APPPATH/Database/Migrations/%s_People\\.php\"?\nFile exists: \"APPPATH/Database/Seeds/People\\.php\"\n$#",
                self::TIMESTAMP,
            ),
            $this->getUndecoratedBuffer(),
        );
    }

    public function testCreateComponentWithOptionNamespace(): void
    {
        command('make:scaffold product -n App');

        $this->assertStringContainsString('namespace App\Controllers;', $this->getContents('Controllers/Product.php'));
        $this->assertStringContainsString('namespace App\Models;', $this->getContents('Models/Product.php'));
        $this->assertStringContainsString('namespace App\Database\Seeds;', $this->getContents('Database/Seeds/Product.php'));

        $migrations = glob(APPPATH . 'Database/Migrations/*_Product.php');
        $this->assertCount(1, $migrations);

        $migration = file_get_contents($migrations[0]);
        $this->assertIsString($migration);
        $this->assertStringContainsString('namespace App\Database\Migrations;', $migration);
    }

    public function testPromptsOnceForMissingName(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['people']);
        CLI::setInputOutput($io);

        command('make:scaffold');

        $this->assertSame(1, substr_count($io->getOutput(), 'Class name'));
        $this->assertFileExists(APPPATH . 'Controllers/People.php');
        $this->assertFileExists(APPPATH . 'Database/Seeds/People.php');
    }
}
