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
final class ModelGeneratorTest extends CIUnitTestCase
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

        foreach (['User.php', 'Cars.php', 'UserModel.php', 'MyTableModel.php', 'Bogus.php'] as $file) {
            if (is_file(APPPATH . 'Models/' . $file)) {
                unlink(APPPATH . 'Models/' . $file);
            }
        }

        helper('filesystem');

        foreach ([APPPATH . 'Models/Admin', APPPATH . 'Entities'] as $dir) {
            if (is_dir($dir)) {
                delete_files($dir, true, false, true);
                rmdir($dir);
            }
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

    public function testGenerateModel(): void
    {
        command('make:model user');

        $this->assertSame("\nFile created: APPPATH/Models/User.php\n", $this->getUndecoratedBuffer());

        $contents = $this->getContents('Models/User.php');
        $this->assertStringContainsString('class User extends Model', $contents);
        $this->assertStringContainsString("protected \$table                  = 'users';", $contents);
        $this->assertStringContainsString("protected \$returnType             = 'array';", $contents);
        $this->assertStringNotContainsString('$DBGroup', $contents);
    }

    public function testGenerateModelWithOptionTable(): void
    {
        command('make:model cars --table utilisateur');

        $this->assertStringContainsString("protected \$table                  = 'utilisateur';", $this->getContents('Models/Cars.php'));
    }

    public function testGenerateModelWithOptionDBGroup(): void
    {
        command('make:model user --dbgroup testing');

        $this->assertStringContainsString("protected \$DBGroup                = 'testing';", $this->getContents('Models/User.php'));
    }

    public function testGenerateModelWithOptionReturnObject(): void
    {
        command('make:model user --return object');

        $this->assertStringContainsString("protected \$returnType             = 'object';", $this->getContents('Models/User.php'));
    }

    public function testGenerateModelWithOptionReturnEntity(): void
    {
        command('make:model user --return entity');

        $this->assertSame(
            <<<'EOT'

                File created: APPPATH/Models/User.php
                File created: APPPATH/Entities/User.php

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertStringContainsString(
            'protected $returnType             = \App\Entities\User::class;',
            $this->getContents('Models/User.php'),
        );
        $this->assertStringContainsString('class User extends Entity', $this->getContents('Entities/User.php'));
    }

    public function testGenerateModelWithShortcuts(): void
    {
        command('make:model user -t people -g testing -r object');

        $contents = $this->getContents('Models/User.php');
        $this->assertStringContainsString("protected \$DBGroup                = 'testing';", $contents);
        $this->assertStringContainsString("protected \$table                  = 'people';", $contents);
        $this->assertStringContainsString("protected \$returnType             = 'object';", $contents);
    }

    public function testGenerateModelWithOptionSuffix(): void
    {
        command('make:model user --suffix --return entity');

        $this->assertStringContainsString(
            'protected $returnType             = \App\Entities\UserEntity::class;',
            $this->getContents('Models/UserModel.php'),
        );
        $this->assertFileExists(APPPATH . 'Entities/UserEntity.php');
    }

    public function testGenerateModelWithSubNamespaceAndReturnEntity(): void
    {
        command('make:model admin/class --return entity');

        $this->assertStringContainsString(
            'protected $returnType             = \App\Entities\Admin\Class::class;',
            $this->getContents('Models/Admin/Class.php'),
        );
        $this->assertStringContainsString('namespace App\Entities\Admin;', $this->getContents('Entities/Admin/Class.php'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5050
     */
    public function testGenerateModelWithSuffixAndMixedPascalCasedName(): void
    {
        command('make:model MyTable --suffix --return entity');

        $this->assertFileExists(APPPATH . 'Models/MyTableModel.php');
        $this->assertFileExists(APPPATH . 'Entities/MyTableEntity.php');
    }

    public function testEntityIsNotGeneratedWhenModelExists(): void
    {
        command('make:model user');
        $this->resetStreamFilterBuffer();

        command('make:model user --return entity');

        $this->assertSame("File exists: \"APPPATH/Models/User.php\"\n", $this->getUndecoratedBuffer());
        $this->assertFileDoesNotExist(APPPATH . 'Entities/User.php');
    }

    public function testForceIsForwardedToEntity(): void
    {
        command('make:model user --return entity');
        $this->resetStreamFilterBuffer();

        command('make:model user --return entity --force');

        $this->assertSame(
            <<<'EOT'
                File overwritten: "APPPATH/Models/User.php"
                File overwritten: "APPPATH/Entities/User.php"

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testInvalidReturnTypeIsRejectedWhenNotInteractive(): void
    {
        command('make:model bogus --return json --no-interaction');

        $this->assertSame("\nReturn type \"json\" is not valid.\n", $this->getUndecoratedBuffer());
        $this->assertFileDoesNotExist(APPPATH . 'Models/Bogus.php');
    }

    public function testInvalidReturnTypePromptsWhenInteractive(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['object']);
        CLI::setInputOutput($io);

        $command = new ModelGenerator(new Commands());
        $command->setInteractive(true);

        $this->assertSame(EXIT_SUCCESS, $command->run(['user'], ['return' => 'json']));
        $this->assertStringContainsString('Return type', $io->getOutput());
        $this->assertStringContainsString("protected \$returnType             = 'object';", $this->getContents('Models/User.php'));
    }
}
