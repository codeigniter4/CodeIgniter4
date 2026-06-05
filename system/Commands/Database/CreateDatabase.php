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

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use Config\Database;
use Throwable;

/**
 * Creates a new database.
 */
#[Command(name: 'db:create', description: 'Create a new database schema.', group: 'Database')]
class CreateDatabase extends AbstractCommand
{
    /**
     * @var list<string>
     */
    private const VALID_EXTENSIONS = ['db', 'sqlite'];

    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(
                name: 'db_name',
                description: 'The database name to use.',
                required: true,
            ))
            ->addOption(new Option(
                name: 'ext',
                description: 'File extension of the database file for SQLite3. Can be `db` or `sqlite`.',
                requiresValue: true,
                default: 'db',
            ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if ($arguments === []) {
            $arguments[] = CLI::prompt('Database name', null, 'required');
        }

        $ext = $this->getUnboundOption('ext', $options);

        if (is_string($ext) && ! in_array($ext, self::VALID_EXTENSIONS, true)) {
            $options['ext'] = CLI::prompt('Please choose a valid file extension', self::VALID_EXTENSIONS, 'required');
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        $name = $arguments['db_name'];
        assert(is_string($name));

        try {
            $config = config(Database::class);

            // Set to an empty database to prevent connection errors.
            $group = service('environment')->isTesting() ? 'tests' : $config->defaultGroup;

            $config->{$group}['database'] = '';

            $db = Database::connect();

            if ($db instanceof SQLite3Connection) {
                $ext = $options['ext'];
                assert(is_string($ext));

                if (! in_array($ext, self::VALID_EXTENSIONS, true)) {
                    CLI::error(sprintf('Invalid file extension "%s". Use either `db` or `sqlite`.', $ext), 'light_gray', 'red');

                    return EXIT_ERROR;
                }

                if ($name !== ':memory:') {
                    $name = str_replace(['.db', '.sqlite'], '', $name) . ".{$ext}";
                }

                $config->{$group}['DBDriver'] = 'SQLite3';
                $config->{$group}['database'] = $name;

                if ($name !== ':memory:') {
                    $dbName = str_contains($name, DIRECTORY_SEPARATOR) ? $name : WRITEPATH . $name;

                    if (is_file($dbName)) {
                        CLI::error("Database \"{$dbName}\" already exists.", 'light_gray', 'red');

                        return EXIT_ERROR;
                    }

                    unset($dbName);
                }

                // Connect to new SQLite3 to create new database
                $db = Database::connect(null, false);
                $db->connect();

                if (! is_file($db->getDatabase()) && $name !== ':memory:') {
                    // @codeCoverageIgnoreStart
                    CLI::error('Database creation failed.', 'light_gray', 'red');

                    return EXIT_ERROR;
                    // @codeCoverageIgnoreEnd
                }
            } elseif (! Database::forge()->createDatabase($name)) {
                CLI::error('Database creation failed.', 'light_gray', 'red');

                return EXIT_ERROR;
            }

            CLI::write("Database \"{$name}\" successfully created.", 'green');

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->renderThrowable($e);

            return EXIT_ERROR;
        } finally {
            Factories::reset('config');
            Database::connect(null, false);
        }
    }
}
