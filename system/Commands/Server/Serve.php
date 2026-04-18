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
 *
 * @codeCoverageIgnore
 */
#[Command(name: 'serve', description: 'Launches the CodeIgniter PHP-Development Server.', group: 'CodeIgniter')]
class Serve extends AbstractCommand
{
    /**
     * The number of times to retry if the port is already in use.
     */
    private const RETRIES = 10;

    protected function configure(): void
    {
        $this
            ->addOption(new Option(name: 'php', description: 'The PHP binary to use.', requiresValue: true, default: PHP_BINARY))
            ->addOption(new Option(name: 'host', description: 'The host to serve on.', requiresValue: true, default: 'localhost'))
            ->addOption(new Option(name: 'port', description: 'The port to serve on.', requiresValue: true, default: '8080'));
    }

    protected function execute(array $arguments, array $options): int
    {
        $basePort = (int) $options['port'];
        $status   = EXIT_SUCCESS;

        for ($offset = 0; $offset <= self::RETRIES; $offset++) {
            $port = $basePort + $offset;

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

            if ($status === EXIT_SUCCESS) {
                return $status;
            }

            CLI::newLine();
        }

        return $status;
    }
}
