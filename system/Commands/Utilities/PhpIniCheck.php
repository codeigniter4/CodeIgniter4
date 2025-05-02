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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Security\CheckPhpIni;

/**
 * Check php.ini values.
 */
final class PhpIniCheck extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'phpini:check';

    /**
     * The Command's short description
     *
     * @var string
     */
    protected $description = 'Check your php.ini values in production environment.';

    /**
     * The Command's usage
     *
     * @var string
     */
    protected $usage = 'phpini:check';

    /**
     * The Command's arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'opcache' => 'Check detail opcache values in production environment.',
    ];

    /**
     * The Command's options
     *
     * @var array<string, string>
     */
    protected $options = [];

    /**
     * @return int
     */
    public function run(array $params)
    {
        if (isset($params[0]) && ! in_array($params[0], array_keys($this->arguments), true)) {
            CLI::error('You must specify a correct argument.');
            CLI::write('    Usage: ' . $this->usage);
            CLI::write('  Example: phpini:check opcache');
            CLI::write('Arguments:');

            $length = max(array_map(strlen(...), array_keys($this->arguments)));

            foreach ($this->arguments as $argument => $description) {
                CLI::write(CLI::color($this->setPad($argument, $length, 2, 2), 'green') . $description);
            }

            return EXIT_ERROR;
        }

        $argument = $params[0] ?? null;

        CheckPhpIni::run(argument: $argument);

        return EXIT_SUCCESS;
    }
}
