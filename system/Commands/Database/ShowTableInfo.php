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
use Config\Database;

/**
 * Get table data if it exists in the database.
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
    protected $usage = 'db:table <table_name> [options]';

    /**
     * The Command's arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'table_name' => 'The table name for show info',
    ];

    /**
     * The Command's options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--show'               => 'List the names of all database tables.',
        '--metadata'           => 'Retrieves list of containing field information.',
        '--desc'               => 'Sort the table rows in DESC order.',
        '--limit-rows'         => 'Limit the number of rows. [Default = 10]',
        '--limit-fields-value' => 'Custom length Limit of field values. [Default = 15]',
    ];

    public function run(array $params)
    {
        $this->db = Database::connect();

        $tables = $this->db->listTables();

        $this->sortIsDESC = CLI::getOption('desc');

        if ($tables === []) {
            CLI::error('Database has no tables!', 'light_gray', 'red');
            CLI:newLine();

            return;
        }

        if (CLI::getOption('show')) {
            CLI::write('The following is a list of the names of all database tables:', 'black', 'yellow');
            CLI::newLine();

            $thead = ['ID', 'Table name', 'Number of rows', 'Number of fields'];
            $tbody = $this->makeTbodyForShowAllTables($tables);

            CLI::table($tbody, $thead);
            CLI::newLine();

            return;
        }

        $tableName = array_shift($params);

        if (in_array($tableName, $tables, true)) {
            if (CLI::getOption('metadata')) {
                $this->showFieldMetaData($tableName);

                return;
            }

            CLI::newLine();
            CLI::write("Data of table \"{$tableName}\":", 'black', 'yellow');
            CLI::newLine();

            $thead = $this->db->getFieldNames($tableName);
            $tbody = $this->makeTableRows($tableName);
            CLI::table($tbody, $thead);

            return;
        }

        $tableName = CLI::promptByKey(['Here is the list of your database tables:', 'Which table do you want to see?'], $tables, 'required');

        if (CLI::getOption('metadata')) {
            $this->showFieldMetaData($tables[$tableName]);

            return;
        }

        CLI::newLine();
        CLI::write("Data of table \"{$tables[$tableName]}\":", 'black', 'yellow');
        CLI::newLine();

        $thead = $this->db->getFieldNames($tables[$tableName]);
        $tbody = $this->makeTableRows($tables[$tableName]);
        CLI::table($tbody, $thead);
    }

    private function makeTbodyForShowAllTables(array $tables): array
    {
        foreach ($tables  as $id => $tableName) {
            $db = $this->db->query("SELECT * FROM {$tableName}");

            $tbody[] = [
                $id + 1,
                $tableName,
                $db->getNumRows(),
                $db->getFieldCount(),

            ];
        }

        if ($this->sortIsDESC) {
            krsort($tbody);
        }

        return $tbody;
    }

    private function makeTableRows(string $tableName): array
    {
        $limitRows = (int) CLI::getOption('limit-rows');

        if (in_array($limitRows, [null, true, 0, 1], true)) {
            $limitRows = 10;
        }

        $limitFieldsValue = (int) CLI::getOption('limit-fields-value');

        if (in_array($limitFieldsValue, [null, true, 0, 1], true)) {
            $limitFieldsValue = 15;
        }

        $tbody = [];

        $customQueryForEachField = '';

        $fieldNames = $this->db->getFieldNames($tableName);

        foreach ($fieldNames as $fieldName) {
            $customQueryForEachField .= ",IF(length(`{$fieldName}`) > {$limitFieldsValue} ,CONCAT(SUBSTRING(`{$fieldName}`, 1, {$limitFieldsValue}),'...'), `{$fieldName}` ) as `{$fieldName}`";
        }

        $rows = $this->db->query('SELECT * ' . $customQueryForEachField . " FROM {$tableName} LIMIT {$limitRows}")->getResultArray();

        foreach ($rows as $row) {
            $tbody[] = $row;
        }

        if ($this->sortIsDESC) {
            krsort($tbody);
        }

        return $tbody;
    }

    protected function showFieldMetaData(string $tableName): void
    {
        CLI::newLine();
        CLI::write("List of metadata information in table \"{$tableName}\":", 'black', 'yellow');
        CLI::newLine();

        $thead = ['field name', 'type', 'max_length', 'nullable', 'default', 'primary_key'];

        $fields = $this->db->getFieldData($tableName);

        foreach ($fields as $row) {
            $tbody[] = [
                $row->name,
                $row->type,
                $row->max_length,
                $this->setYesOrNo($row->nullable),
                $row->default,
                $this->setYesOrNo($row->primary_key),
            ];
        }

        if ($this->sortIsDESC) {
            krsort($tbody);
        }

        CLI::table($tbody, $thead);
    }

    protected function setYesOrNo(bool $fieldValue): string
    {
        if ($fieldValue) {
            return CLI::color('Yes', 'green');
        }

        return CLI::color('No!', 'red');
    }
}
