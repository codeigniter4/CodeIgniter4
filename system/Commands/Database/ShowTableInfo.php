<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Get table data if it exists in the database.
 *
 * @see \CodeIgniter\Commands\Database\ShowTableInfoTest
 */
class ShowTableInfo extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'db:table';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Retrieves information on the selected table.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = <<<'EOL'
        db:table [<table_name>] [options]

          Examples:
            db:table --show
            db:table --metadata
            db:table my_table --metadata
            db:table my_table
            db:table my_table --limit-rows 5 --limit-field-value 10 --desc
        EOL;

    /**
     * The Command's arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'table_name' => 'The table name to show info',
    ];

    /**
     * The Command's options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--show'              => 'Lists the names of all database tables.',
        '--metadata'          => 'Retrieves list containing field information.',
        '--desc'              => 'Sorts the table rows in DESC order.',
        '--limit-rows'        => 'Limits the number of rows. Default: 10.',
        '--limit-field-value' => 'Limits the length of field values. Default: 15.',
    ];

    /**
     * @phpstan-var  list<list<string|int>> Table Data.
     */
    private array $tbody;

    private BaseConnection $db;

    /**
     * @var bool Sort the table rows in DESC order or not.
     */
    private bool $sortDesc = false;

    private string $DBPrefix;

    public function run(array $params)
    {
        $this->db       = Database::connect();
        $this->DBPrefix = $this->db->getPrefix();

        $tables = $this->db->listTables();

        if (array_key_exists('desc', $params)) {
            $this->sortDesc = true;
        }

        if ($tables === []) {
            CLI::error('Database has no tables!', 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        if (array_key_exists('show', $params)) {
            $this->showAllTables($tables);

            return;
        }

        $tableName       = $params[0] ?? null;
        $limitRows       = (int) ($params['limit-rows'] ?? 10);
        $limitFieldValue = (int) ($params['limit-field-value'] ?? 15);

        if (! in_array($tableName, $tables, true)) {
            $tableNameNo = CLI::promptByKey(
                ['Here is the list of your database tables:', 'Which table do you want to see?'],
                $tables,
                'required'
            );
            CLI::newLine();

            $tableName = $tables[$tableNameNo];
        }

        if (array_key_exists('metadata', $params)) {
            $this->showFieldMetaData($tableName);

            return;
        }

        $this->showDataOfTable($tableName, $limitRows, $limitFieldValue);
    }

    private function removeDBPrefix(): void
    {
        $this->db->setPrefix('');
    }

    private function restoreDBPrefix(): void
    {
        $this->db->setPrefix($this->DBPrefix);
    }

    private function showDataOfTable(string $tableName, int $limitRows, int $limitFieldValue)
    {
        CLI::write("Data of Table \"{$tableName}\":", 'black', 'yellow');
        CLI::newLine();

        $this->removeDBPrefix();
        $thead = $this->db->getFieldNames($tableName);
        $this->restoreDBPrefix();

        // If there is a field named `id`, sort by it.
        $sortField = null;
        if (in_array('id', $thead, true)) {
            $sortField = 'id';
        }

        $this->tbody = $this->makeTableRows($tableName, $limitRows, $limitFieldValue, $sortField);
        CLI::table($this->tbody, $thead);
    }

    private function showAllTables(array $tables)
    {
        CLI::write('The following is a list of the names of all database tables:', 'black', 'yellow');
        CLI::newLine();

        $thead       = ['ID', 'Table Name', 'Num of Rows', 'Num of Fields'];
        $this->tbody = $this->makeTbodyForShowAllTables($tables);

        CLI::table($this->tbody, $thead);
        CLI::newLine();
    }

    private function makeTbodyForShowAllTables(array $tables): array
    {
        $this->removeDBPrefix();

        foreach ($tables  as $id => $tableName) {
            $table = $this->db->protectIdentifiers($tableName);
            $db    = $this->db->query("SELECT * FROM {$table}");

            $this->tbody[] = [
                $id + 1,
                $tableName,
                $db->getNumRows(),
                $db->getFieldCount(),
            ];
        }

        $this->restoreDBPrefix();

        if ($this->sortDesc) {
            krsort($this->tbody);
        }

        return $this->tbody;
    }

    private function makeTableRows(
        string $tableName,
        int $limitRows,
        int $limitFieldValue,
        ?string $sortField = null
    ): array {
        $this->tbody = [];

        $this->removeDBPrefix();
        $builder = $this->db->table($tableName);
        $builder->limit($limitRows);
        if ($sortField !== null) {
            $builder->orderBy($sortField, $this->sortDesc ? 'DESC' : 'ASC');
        }
        $rows = $builder->get()->getResultArray();
        $this->restoreDBPrefix();

        foreach ($rows as $row) {
            $row = array_map(
                static fn ($item): string => mb_strlen((string) $item) > $limitFieldValue
                    ? mb_substr((string) $item, 0, $limitFieldValue) . '...'
                    : (string) $item,
                $row
            );
            $this->tbody[] = $row;
        }

        if ($sortField === null && $this->sortDesc) {
            krsort($this->tbody);
        }

        return $this->tbody;
    }

    private function showFieldMetaData(string $tableName): void
    {
        CLI::write("List of Metadata Information in Table \"{$tableName}\":", 'black', 'yellow');
        CLI::newLine();

        $thead = ['Field Name', 'Type', 'Max Length', 'Nullable', 'Default', 'Primary Key'];

        $this->removeDBPrefix();
        $fields = $this->db->getFieldData($tableName);
        $this->restoreDBPrefix();

        foreach ($fields as $row) {
            $this->tbody[] = [
                $row->name,
                $row->type,
                $row->max_length,
                isset($row->nullable) ? $this->setYesOrNo($row->nullable) : 'n/a',
                $row->default,
                isset($row->primary_key) ? $this->setYesOrNo($row->primary_key) : 'n/a',
            ];
        }

        if ($this->sortDesc) {
            krsort($this->tbody);
        }

        CLI::table($this->tbody, $thead);
    }

    private function setYesOrNo(bool $fieldValue): string
    {
        if ($fieldValue) {
            return CLI::color('Yes', 'green');
        }

        return CLI::color('No', 'red');
    }
}
