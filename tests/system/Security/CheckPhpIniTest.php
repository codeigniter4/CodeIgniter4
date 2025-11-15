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

namespace CodeIgniter\Security;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CheckPhpIniTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public function testCheckIni(): void
    {
        $output = self::getPrivateMethodInvoker(CheckPhpIni::class, 'checkIni')();

        $expected = [
            'global'      => 'UTF-8',
            'current'     => 'UTF-8',
            'recommended' => 'UTF-8',
            'remark'      => '',
        ];
        $this->assertSame($expected, $output['default_charset']);
    }

    public function testCheckIniOpcache(): void
    {
        $output = self::getPrivateMethodInvoker(CheckPhpIni::class, 'checkIni')('opcache');

        $expected = [
            'global'      => '1',
            'current'     => '1',
            'recommended' => '0',
            'remark'      => 'Enable when your code requires to read docblock annotations at runtime',
        ];
        $this->assertSame($expected, $output['opcache.save_comments']);
    }

    public function testRunCli(): void
    {
        CheckPhpIni::run(true);

        $this->assertMatchesRegularExpression(
            '/\| Directive\s+\| Global\s+\| Current\s+\| Recommended\s+\| Remark\s+\|/',
            $this->getStreamFilterBuffer(),
        );
        $this->assertMatchesRegularExpression(
            '/\| default_charset\s+\| UTF-8\s+\| UTF-8\s+\| UTF-8\s+\| \s+\|/',
            $this->getStreamFilterBuffer(),
        );
    }

    public function testRunWeb(): void
    {
        $output = CheckPhpIni::run(false);

        $this->assertIsString($output);
        $this->assertMatchesRegularExpression(
            '/<table border="1" cellpadding="4" cellspacing="0">/',
            $output,
        );
        $this->assertMatchesRegularExpression(
            '/<th>Directive<\/th><th>Global<\/th><th>Current<\/th><th>Recommended<\/th><th>Remark<\/th>/',
            $output,
        );
        $this->assertMatchesRegularExpression(
            '/<td>default_charset<\/td><td>UTF-8<\/td><td>UTF-8<\/td><td>UTF-8<\/td><td><\/td>/',
            $output,
        );
    }
}
