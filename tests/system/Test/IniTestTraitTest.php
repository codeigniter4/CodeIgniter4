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

namespace CodeIgniter\Test;

use ReflectionException;

/**
 * @internal
 *
 * @group Others
 */
final class IniTestTraitTest extends CIUnitTestCase
{
    use IniTestTrait;

    /**
     * @throws ReflectionException
     */
    public function testBackupAndRestoreIniValues(): void
    {
        $this->backupIniValues(['highlight.default']);
        $backup = $this->getPrivateProperty($this, 'iniSettings');
        $this->assertSame('#0000BB', $backup['highlight.default']);

        ini_set('highlight.default', '#FFFFFF');
        $this->assertSame('#FFFFFF', ini_get('highlight.default'));

        $this->restoreIniValues();
        $this->assertSame('#0000BB', ini_get('highlight.default'));

        $backup = $this->getPrivateProperty($this, 'iniSettings');
        $this->assertSame([], $backup);
    }
}
