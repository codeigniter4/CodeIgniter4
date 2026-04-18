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

namespace CodeIgniter\Commands\Server;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;

/**
 * Launches the CodeIgniter PHP-Development Server.
 */
#[Command(name: 'serve', description: 'Launches the CodeIgniter PHP-Development Server.', group: 'CodeIgniter')]
class Serve extends AbstractCommand
{
    /**
     * The current port offset.
     */
    private int $portOffset = 0;

    /**
     * The number of times to retry if the port is already in use.
     */
    private int $retries = 10;

    protected function configure(): void
    {
        $this
            ->addOption(new Option(name: 'php', description: 'The PHP binary to use.', acceptsValue: true, default: PHP_BINARY))
            ->addOption(new Option(name: 'host', description: 'The host to serve on.', acceptsValue: true, default: 'localhost'))
            ->addOption(new Option(name: 'port', description: 'The port to serve on.', acceptsValue: true, default: '8080'));
    }

    protected function execute(array $arguments, array $options): int
    {
        $port = (int) $options['port'] + $this->portOffset;

        CLI::write(sprintf('CodeIgniter development server started on http://%s:%s', $options['host'], $port), 'green');
        CLI::write('Press Control-C to stop.');
        CLI::newLine();

        passthru(
            sprintf(
                '%s -S %s:%s -t %s %s',
                escapeshellarg($options['php']),
                escapeshellarg($options['host']),
                escapeshellarg((string) $port),
                escapeshellarg(FCPATH),
                escapeshellarg(SYSTEMPATH . 'rewrite.php'),
            ),
            $status,
        );

        if ($status !== EXIT_SUCCESS && $this->portOffset < $this->retries) {
            CLI::newLine();
            $this->portOffset++;

            return $this->execute($arguments, $options);
        }

        return $status;
    }
}
