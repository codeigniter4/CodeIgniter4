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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class PhpIniCheckTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->resetServices();
        parent::tearDown();
    }

    protected function getBuffer(): string
    {
        return $this->getStreamFilterBuffer();
    }

    public function testCommandCheckNoArg(): void
    {
        command('phpini:check');

        $result = $this->getBuffer();

        $this->assertStringContainsString('Directive', $result);
        $this->assertStringContainsString('Global', $result);
        $this->assertStringContainsString('Current', $result);
        $this->assertStringContainsString('Recommended', $result);
        $this->assertStringContainsString('Remark', $result);
    }

    public function testCommandCheckOpcache(): void
    {
        command('phpini:check opcache');

        $this->assertStringContainsString('opcache.save_comments', $this->getBuffer());
    }

    public function testCommandCheckNoExistsArg(): void
    {
        command('phpini:check noexists');

        $this->assertStringContainsString(
            'You must specify a correct argument.',
            $this->getBuffer(),
        );
    }
}
