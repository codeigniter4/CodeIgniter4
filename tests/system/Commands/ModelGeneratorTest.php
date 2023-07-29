<?php

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

/**
 * @internal
 *
 * @group Others
 */
final class ModelGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        parent::tearDown();

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', $this->getStreamFilterBuffer());
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));

        if (is_file($file)) {
            unlink($file);
        }
    }

    private function getFileContent(string $filepath): string
    {
        if (! is_file($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testGenerateModel(): void
    {
        command('make:model user --table users');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends Model', $this->getFileContent($file));
        $this->assertStringContainsString('protected $table            = \'users\';', $this->getFileContent($file));
        $this->assertStringContainsString('protected $DBGroup          = \'default\';', $this->getFileContent($file));
        $this->assertStringContainsString('protected $returnType       = \'array\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionTable(): void
    {
        command('make:model cars -table utilisateur');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Models/Cars.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $table            = \'utilisateur\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionDBGroup(): void
    {
        command('make:model user -dbgroup testing');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $DBGroup          = \'testing\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionReturnArray(): void
    {
        command('make:model user --return array');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $returnType       = \'array\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionReturnObject(): void
    {
        command('make:model user --return object');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $returnType       = \'object\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionReturnEntity(): void
    {
        command('make:model user --return entity');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());

        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $returnType       = \App\Entities\User::class;', $this->getFileContent($file));

        if (is_file($file)) {
            unlink($file);
        }

        $file = APPPATH . 'Entities/User.php';
        $this->assertFileExists($file);
        $dir = dirname($file);

        if (is_file($file)) {
            unlink($file);
        }

        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    public function testGenerateModelWithOptionSuffix(): void
    {
        command('make:model user --suffix --return entity');

        $model  = APPPATH . 'Models/UserModel.php';
        $entity = APPPATH . 'Entities/UserEntity.php';

        $this->assertFileExists($model);
        $this->assertFileExists($entity);

        unlink($model);
        unlink($entity);
        rmdir(dirname($entity));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5050
     */
    public function testGenerateModelWithSuffixAndMixedPascalCasedName(): void
    {
        command('make:model MyTable --suffix --return entity');

        $model  = APPPATH . 'Models/MyTableModel.php';
        $entity = APPPATH . 'Entities/MyTableEntity.php';

        $this->assertFileExists($model);
        $this->assertFileExists($entity);

        unlink($model);
        unlink($entity);
        rmdir(dirname($entity));
    }
}
