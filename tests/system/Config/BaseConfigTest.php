<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Encryption;
use RegistrarConfig;
use RuntimeException;
use SimpleConfig;
use Tests\Support\Config\BadRegistrar;
use Tests\Support\Config\TestRegistrar;

/**
 * @internal
 *
 * @group SeparateProcess
 */
final class BaseConfigTest extends CIUnitTestCase
{
    private string $fixturesFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturesFolder = __DIR__ . '/fixtures';

        if (! class_exists('SimpleConfig', false)) {
            require $this->fixturesFolder . '/SimpleConfig.php';
        }

        if (! class_exists('RegistrarConfig', false)) {
            require $this->fixturesFolder . '/RegistrarConfig.php';
        }

        if (! class_exists('Encryption', false)) {
            require $this->fixturesFolder . '/Encryption.php';
        }
    }

    public function testBasicValues(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();
        $config = new SimpleConfig();

        $this->assertNull($config->FOO);
        $this->assertSame('', $config->echo);
        $this->assertTrue($config->foxtrot);
        $this->assertSame(18, $config->golf);
    }

    public function testUseDefaultValueTypeIntAndFloatValues(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();
        $config = new SimpleConfig();

        $this->assertSame(0.0, $config->float);
        $this->assertSame(999, $config->int);
    }

    public function testUseDefaultValueTypeStringValue(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();
        $config = new SimpleConfig();

        $this->assertSame('123456', $config->password);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testServerValues(): void
    {
        $_SERVER = [
            'simpleconfig.shortie' => 123,
            'SimpleConfig.longie'  => 456,
        ];
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();
        $config = new SimpleConfig();

        $this->assertSame('123', $config->shortie);
        $this->assertSame('456', $config->longie);
    }

    public function testEnvironmentOverrides(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();

        $config = new SimpleConfig();

        // override config with ENV var
        $this->assertSame('pow', $config->alpha);
        // config should not be over-written by wrongly named ENV var
        $this->assertSame('three', $config->charlie);
        // override config with shortPrefix ENV var
        $this->assertSame('hubbahubba', $config->delta);
        // incorrect env name should not inject property
        $this->assertFalse(property_exists($config, 'notthere'));
        // empty ENV var should not affect config setting
        $this->assertSame('pineapple', $config->fruit);
        // non-empty ENV var should overrideconfig setting
        $this->assertSame('banana', $config->dessert);
        // null property should not be affected
        $this->assertNull($config->QEMPTYSTR);
        // property name with underscore
        $this->assertSame('bar', $config->onedeep_value);
        // array property name with underscore and key with underscore
        $this->assertSame('foo', $config->one_deep['under_deep']);

        // The default property value is null but has type
        $this->assertSame(20, $config->size);
    }

    public function testPrefixedValues(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();

        $config = new SimpleConfig();

        $this->assertSame('baz', $config->onedeep);
    }

    public function testPrefixedArrayValues(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();

        $config = new SimpleConfig();

        $this->assertSame('ci4', $config->default['name']);
        $this->assertSame('Malcolm', $config->crew['captain']);
        $this->assertSame('Spock', $config->crew['science']);
        $this->assertArrayNotHasKey('pilot', $config->crew);
        $this->assertTrue($config->crew['comms']);
        $this->assertFalse($config->crew['doctor']);
    }

    public function testArrayValues(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();

        $config = new SimpleConfig();

        $this->assertSame('complex', $config->simple['name']);
        $this->assertSame('foo', $config->first);
        $this->assertSame('bar', $config->second);
    }

    public function testSetsDefaultValues(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'commented.env');
        $dotenv->load();

        $config = new SimpleConfig();

        $this->assertSame('foo', $config->first);
        $this->assertSame('bar', $config->second);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetsDefaultValuesEncryptionUsingHex2Bin(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'encryption.env');
        $dotenv->load();
        $config = new Encryption();

        // override config with ENV var
        $this->assertSame('f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6', bin2hex($config->key));
        $this->assertSame('OpenSSL', $config->driver);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetDefaultValuesEncryptionUsingBase64(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'base64encryption.env');
        $dotenv->load();
        $config = new Encryption('base64');

        $this->assertSame('L40bKo6b8Nu541LeVeZ1i5RXfGgnkar42CPTfukhGhw=', base64_encode($config->key));
        $this->assertSame('OpenSSL', $config->driver);
    }

    public function testSetsDefaultValuesHex2Bin(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'commented.env');
        $dotenv->load();
        $config = new Encryption();

        // override config with ENV var
        $this->assertSame('84cf2c0811d5daf9e1c897825a3debce91f9a33391e639f72f7a4740b30675a2', bin2hex($config->key));
        $this->assertSame('MCrypt', $config->driver);
    }

    public function testSetDefaultValuesBase64(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'commented.env');
        $dotenv->load();
        $config = new Encryption('base64');

        $this->assertSame('Psf8bUHRh1UJYG2M7e+5ec3MdjpKpzAr0twamcAvOcI=', base64_encode($config->key));
        $this->assertSame('MCrypt', $config->driver);
    }

    public function testRecognizesLooseValues(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'loose.env');
        $dotenv->load();

        $config = new SimpleConfig();

        $this->assertSame(0, (int) $config->QZERO);
        $this->assertSame('0', $config->QZEROSTR);
        $this->assertSame(' ', $config->QEMPTYSTR);
        $this->assertFalse($config->QFALSE);
    }

    public function testRegistrars(): void
    {
        $config              = new RegistrarConfig();
        $config::$registrars = [TestRegistrar::class];
        $this->setPrivateProperty($config, 'didDiscovery', true);
        $method = $this->getPrivateMethodInvoker($config, 'registerProperties');
        $method();

        // no change to unmodified property
        $this->assertSame('bar', $config->foo);
        // add to an existing array property
        $this->assertSame(['baz', 'first', 'second'], $config->bar);
    }

    public function testBadRegistrar(): void
    {
        // Shouldn't change any values.
        $config              = new RegistrarConfig();
        $config::$registrars = [BadRegistrar::class];
        $this->setPrivateProperty($config, 'didDiscovery', true);

        $this->expectException(RuntimeException::class);
        $method = $this->getPrivateMethodInvoker($config, 'registerProperties');
        $method();

        $this->assertSame('bar', $config->foo);
    }

    public function testNotEnabled(): void
    {
        $modulesConfig          = config('Modules');
        $modulesConfig->enabled = false;

        $config              = new RegistrarConfig();
        $config::$registrars = [];
        $expected            = $config::$registrars;

        $method = $this->getPrivateMethodInvoker($config, 'registerProperties');
        $method();

        $this->assertSame($expected, $config::$registrars);
    }

    public function testDidDiscovery(): void
    {
        $modulesConfig          = config('Modules');
        $modulesConfig->enabled = true;

        $config              = new RegistrarConfig();
        $config::$registrars = [];
        $this->setPrivateProperty($config, 'didDiscovery', false);

        $method = $this->getPrivateMethodInvoker($config, 'registerProperties');
        $method();

        $this->assertTrue($this->getPrivateProperty($config, 'didDiscovery'));
    }
}
