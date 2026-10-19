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

namespace Tests\Support\Commands\Modern;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CLI\Verbosity;

#[Command(name: 'test:verbosity', description: 'Fixture that writes one line per verbosity level.', group: 'Fixtures')]
final class VerbosityProbeCommand extends AbstractCommand
{
    /**
     * @var array{silent?: bool, quiet?: bool, verbose?: bool}
     */
    public static array $observedOptions = [];

    public static false|string $observedShellVerbosity = false;
    public static bool $observedOnce                   = false;

    public static function reset(): void
    {
        self::$observedOptions        = [];
        self::$observedShellVerbosity = false;
        self::$observedOnce           = false;
    }

    protected function configure(): void
    {
        $this->addOption(new Option(name: 'once', shortcut: '1', description: 'Numeric shortcut.'));
    }

    protected function execute(array $arguments, array $options): int
    {
        self::$observedOnce = $options['once'] === true;

        self::$observedOptions = [
            'silent'  => $options['silent'] === true,
            'quiet'   => $options['quiet'] === true,
            'verbose' => $options['verbose'] === true,
        ];
        self::$observedShellVerbosity = getenv('SHELL_VERBOSITY');

        $output = $this->getOutput();

        $output->write('quiet', verbosity: Verbosity::Quiet);
        $output->write('normal');
        $output->write('verbose', verbosity: Verbosity::Verbose);
        $output->write('very verbose', verbosity: Verbosity::VeryVerbose);
        $output->write('debug', verbosity: Verbosity::Debug);
        $output->error('error');

        return EXIT_SUCCESS;
    }
}
