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

namespace CodeIgniter\CLI;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class NullInputOutputTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public function testFwriteDiscardsOutput(): void
    {
        $io = new NullInputOutput();
        $io->fwrite(STDOUT, 'should not appear');
        $io->fwrite(STDERR, 'should not appear either');

        $this->assertSame('', $this->getStreamFilterBuffer());
    }

    public function testInputReturnsEmptyStringWithoutEchoingPrefix(): void
    {
        $io = new NullInputOutput();

        $this->assertSame('', $io->input());
        $this->assertSame('', $io->input('any prefix > '));
        $this->assertSame('', $this->getStreamFilterBuffer());
    }

    public function testCanBeSwappedIntoCliToSilenceWrites(): void
    {
        $prior = CLI::getInputOutput();
        CLI::setInputOutput(new NullInputOutput());

        try {
            CLI::write('this should be discarded');
            CLI::error('this too');
            $this->assertSame('', $this->getStreamFilterBuffer());
        } finally {
            if ($prior instanceof InputOutput) {
                CLI::setInputOutput($prior);
            } else {
                CLI::resetInputOutput();
            }
        }
    }
}
