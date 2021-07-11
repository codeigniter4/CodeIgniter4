<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class ModelGeneratorTest extends CIUnitTestCase
{
    protected $streamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        if (is_file($file)) {
            unlink($file);
        }
    }

    protected function getFileContent(string $filepath): string
    {
        if (! is_file($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testGenerateModel()
    {
        command('make:model user -table users');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends Model', $this->getFileContent($file));
        $this->assertStringContainsString('protected $table                = \'users\';', $this->getFileContent($file));
        $this->assertStringContainsString('protected $DBGroup              = \'default\';', $this->getFileContent($file));
        $this->assertStringContainsString('protected $returnType           = \'array\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionTable()
    {
        command('make:model cars -table utilisateur');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Models/Cars.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $table                = \'utilisateur\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionDBGroup()
    {
        command('make:model user -dbgroup testing');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $DBGroup              = \'testing\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionReturnArray()
    {
        command('make:model user -return array');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $returnType           = \'array\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionReturnObject()
    {
        command('make:model user -return object');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $returnType           = \'object\';', $this->getFileContent($file));
    }

    public function testGenerateModelWithOptionReturnEntity()
    {
        command('make:model user -return entity');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Models/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('protected $returnType           = \'App\Entities\User\';', $this->getFileContent($file));
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

    public function testGenerateModelWithOptionSuffix()
    {
        command('make:model user -suffix -return entity');

        $model  = APPPATH . 'Models/UserModel.php';
        $entity = APPPATH . 'Entities/UserEntity.php';

        $this->assertFileExists($model);
        $this->assertFileExists($entity);
        unlink($model);
        unlink($entity);
        rmdir(dirname($entity));
    }
}
