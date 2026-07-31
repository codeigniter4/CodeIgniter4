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
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\TableName;
use CodeIgniter\Exceptions\InvalidArgumentException;
use Config\Database;

/**
 * Retrieves information on the selected table.
 */
#[Command(
    name: 'db:table',
    description: 'Retrieves information on the selected table.',
    group: 'Database',
)]
class ShowTableInfo extends AbstractCommand
{
    private ?BaseConnection $db = null;

    /**
     * @var string The sort order for table rows.
     */
    private string $sortOrder = 'ASC';

    private string $dbPrefix;

    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(
                name: 'table_name',
                description: 'The table name to show info.',
                default: '',
            ))
            ->addOption(new Option(
                name: 'show',
                description: 'Lists the names of all database tables.',
            ))
            ->addOption(new Option(
                name: 'metadata',
                description: 'Retrieves list containing field information.',
            ))
            ->addOption(new Option(
                name: 'desc',
                description: 'Sorts the table rows in DESC order.',
            ))
            ->addOption(new Option(
                name: 'limit-rows',
                description: 'Limits the number of rows.',
                requiresValue: true,
                valueLabel: 'rows',
                default: '10',
            ))
            ->addOption(new Option(
                name: 'limit-field-value',
                description: 'Limits the length of field values.',
                requiresValue: true,
                valueLabel: 'value',
                default: '15',
            ))
            ->addOption(new Option(
                name: 'dbgroup',
                description: 'Database group to show.',
                requiresValue: true,
                valueLabel: 'group',
                default: '',
            ))
            ->addUsage('db:table --show')
            ->addUsage('db:table --metadata')
            ->addUsage('db:table my_table --metadata')
            ->addUsage('db:table my_table')
            ->addUsage('db:table my_table --limit-rows 5 --limit-field-value 10 --desc');
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if ($this->hasUnboundOption('show', $options)) {
            return;
        }

        try {
            $db = Database::connect($this->resolveDbGroup($this->getUnboundOption('dbgroup', $options)));
        } catch (InvalidArgumentException) {
            return;
        }

        $tables = $db->listTables();

        if ($tables === false || $tables === []) {
            return;
        }

        while (! in_array($arguments[0] ?? '', $tables, true)) {
            $tableKey = CLI::promptByKey(
                ['Here is the list of your database tables:', 'Which table do you want to see?'],
                $tables,
                'required',
            );
            CLI::newLine();

            $arguments[0] = $tables[$tableKey] ?? '';
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        try {
            $this->db = Database::connect($this->resolveDbGroup($options['dbgroup']));
        } catch (InvalidArgumentException $e) {
            CLI::error($e->getMessage());

            return EXIT_ERROR;
        }

        $this->dbPrefix = $this->db->getPrefix();

        $this->showDbConfig();

        $tables = $this->db->listTables();

        $this->sortOrder = $options['desc'] === true ? 'DESC' : 'ASC';

        if ($tables === false || $tables === []) {
            CLI::error('Database has no tables!', 'light_gray', 'red');

            return EXIT_ERROR;
        }

        if ($options['show'] === true) {
            $this->showAllTables($tables);

            return EXIT_SUCCESS;
        }

        $tableName = $arguments['table_name'];
        assert(is_string($tableName));

        if (! in_array($tableName, $tables, true)) {
            CLI::error(
                $tableName === ''
                    ? 'No table name was specified.'
                    : sprintf('Table "%s" was not found in the database.', $tableName),
                'light_gray',
                'red',
            );

            return EXIT_ERROR;
        }

        if ($options['metadata'] === true) {
            $this->showFieldMetaData($tableName);

            return EXIT_SUCCESS;
        }

        $limitRows       = $options['limit-rows'];
        $limitFieldValue = $options['limit-field-value'];
        assert(is_string($limitRows) && is_string($limitFieldValue));

        $this->showDataOfTable($tableName, (int) $limitRows, (int) $limitFieldValue);

        return EXIT_SUCCESS;
    }

    private function resolveDbGroup(mixed $group): ?string
    {
        return is_string($group) && $group !== '' ? $group : null;
    }

    private function showDbConfig(): void
    {
        CLI::table([[
            $this->db->hostname,
            $this->db->getDatabase(),
            $this->db->username,
            $this->db->getPlatform(),
            $this->dbPrefix,
            $this->db->port,
        ]], ['Hostname', 'Database', 'Username', 'DB Driver', 'DB Prefix', 'Port']);
    }

    private function removeDbPrefix(): void
    {
        $this->db->setPrefix('');
    }

    private function restoreDbPrefix(): void
    {
        $this->db->setPrefix($this->dbPrefix);
    }

    private function showDataOfTable(string $tableName, int $limitRows, int $limitFieldValue): void
    {
        CLI::write(sprintf('Data of "%s" table:', $tableName), 'black', 'yellow');
        CLI::newLine();

        $this->removeDbPrefix();

        $table      = TableName::fromActualName($this->db->getPrefix(), $tableName);
        $fieldNames = $this->db->getFieldNames($table);

        // If there is a field named `id`, sort by it.
        $sortField = in_array('id', $fieldNames, true) ? 'id' : '';

        $builder = $this->db->table($table)->limit($limitRows);

        if ($sortField !== '') {
            $builder->orderBy($sortField, $this->sortOrder);
        }

        $rows = $builder->get()->getResultArray();

        $this->restoreDbPrefix();

        $thead = array_map(ucfirst(...), $fieldNames);

        $tbody = [];

        foreach ($rows as $row) {
            $tbody[] = array_map(
                static fn ($item): string => mb_strlen((string) $item) > $limitFieldValue
                    ? mb_substr((string) $item, 0, $limitFieldValue) . '...'
                    : (string) $item,
                $row,
            );
        }

        if ($sortField === '' && $this->sortOrder === 'DESC') {
            $tbody = array_reverse($tbody);
        }

        CLI::table($tbody, $thead);
    }

    /**
     * @param list<string> $tables
     */
    private function showAllTables(array $tables): void
    {
        CLI::write('The following is a list of the names of all database tables:', 'black', 'yellow');
        CLI::newLine();

        $this->removeDbPrefix();

        $tbody = [];

        foreach ($tables as $id => $tableName) {
            $tbody[] = [
                $id + 1,
                $tableName,
                $this->db->table($tableName)->countAllResults(),
                count($this->db->getFieldData($tableName)),
            ];
        }

        $this->restoreDbPrefix();

        $thead = ['Id', 'Table Name', 'Num of Rows', 'Num of Fields'];

        CLI::table($this->sortOrder === 'DESC' ? array_reverse($tbody) : $tbody, $thead);
    }

    private function showFieldMetaData(string $tableName): void
    {
        CLI::write(sprintf('List of metadata information in "%s" table:', $tableName), 'black', 'yellow');
        CLI::newLine();

        $thead = ['Field Name', 'Type', 'Max Length', 'Nullable?', 'Default', 'Primary Key?'];

        $this->removeDbPrefix();
        $fields = $this->db->getFieldData($tableName);
        $this->restoreDbPrefix();

        $tbody = [];

        foreach ($fields as $row) {
            $tbody[] = [
                $row->name,
                $row->type,
                $row->max_length,
                isset($row->nullable) ? $this->setYesOrNo($row->nullable) : 'n/a',
                $row->default,
                isset($row->primary_key) ? $this->setYesOrNo($row->primary_key) : 'n/a',
            ];
        }

        CLI::table($this->sortOrder === 'DESC' ? array_reverse($tbody) : $tbody, $thead);
    }

    private function setYesOrNo(mixed $fieldValue): string
    {
        return filter_var($fieldValue, FILTER_VALIDATE_BOOL)
            ? CLI::color('Yes', 'green')
            : CLI::color('No', 'red');
    }
}
