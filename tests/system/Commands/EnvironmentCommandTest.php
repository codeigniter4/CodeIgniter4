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
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 *
 * @group Others
 */
final class EnvironmentCommandTest extends CIUnitTestCase
{
    private $streamFilter;
    private string $envPath       = ROOTPATH . '.env';
    private string $backupEnvPath = ROOTPATH . '.env.backup';

    protected function setUp(): void
    {
        parent::setUp();
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');

        if (is_file($this->envPath)) {
            rename($this->envPath, $this->backupEnvPath);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        stream_filter_remove($this->streamFilter);

        if (is_file($this->envPath)) {
            unlink($this->envPath);
        }

        if (is_file($this->backupEnvPath)) {
            rename($this->backupEnvPath, $this->envPath);
        }

        $_SERVER['CI_ENVIRONMENT'] = $_ENV['CI_ENVIRONMENT'] = ENVIRONMENT;
    }

    public function testUsingCommandWithNoArgumentsGivesCurrentEnvironment(): void
    {
        command('env');
        $this->assertStringContainsString('testing', CITestStreamFilter::$buffer);
        $this->assertStringContainsString(ENVIRONMENT, CITestStreamFilter::$buffer);
    }

    public function testProvidingTestingAsEnvGivesErrorMessage(): void
    {
        command('env testing');
        $this->assertStringContainsString('The "testing" environment is reserved for PHPUnit testing.', CITestStreamFilter::$buffer);
    }

    public function testProvidingUnknownEnvGivesErrorMessage(): void
    {
        command('env foobar');
        $this->assertStringContainsString('Invalid environment type "foobar".', CITestStreamFilter::$buffer);
    }

    public function testDefaultShippedEnvIsMissing()
    {
        rename(ROOTPATH . 'env', ROOTPATH . 'lostenv');
        command('env development');
        rename(ROOTPATH . 'lostenv', ROOTPATH . 'env');

        $this->assertStringContainsString('Both default shipped', CITestStreamFilter::$buffer);
        $this->assertStringContainsString('It is impossible to write the new environment type.', CITestStreamFilter::$buffer);
    }

    public function testSettingNewEnvIsSuccess(): void
    {
        // default env file has `production` env in it
        $_SERVER['CI_ENVIRONMENT'] = 'production';
        command('env development');

        $this->assertStringContainsString('Environment is successfully changed to', CITestStreamFilter::$buffer);
        $this->assertStringContainsString('CI_ENVIRONMENT = development', file_get_contents($this->envPath));
    }
}
