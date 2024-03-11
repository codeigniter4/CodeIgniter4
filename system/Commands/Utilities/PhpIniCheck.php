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
    protected $description = 'Check your php.ini values.';

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
    ];

    /**
     * The Command's options
     *
     * @var array<string, string>
     */
    protected $options = [];

    /**
     * {@inheritDoc}
     */
    public function run(array $params)
    {
        CheckPhpIni::run();

        return EXIT_SUCCESS;
    }
}
