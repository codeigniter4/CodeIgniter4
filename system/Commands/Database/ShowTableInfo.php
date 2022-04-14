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
    protected $description = 'Retrieves the selected table information.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'db:table <table_name>';

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
        "--show" => "Retrieves list the names of all database tables."
    ];

    /**
     * get table info. 
     */
    public function run(array $params)
    {
        
        // Connect to database
        $db        = \Config\Database::connect();
        $getTables = $db->listTables();
        // The database does not have a table.
        if ($getTables === []) {
            return CLI::error('Database has no tables!', 'light_gray', 'red');
        }

        // show all tables name
        if (CLI::getOption('show')) {
            CLI::write('list the names of all database tables : ', 'black', 'yellow');
            CLI::write(implode(' , ', $getTables), 'black', 'blue');
            return CLI::newLine();
        }

        $table_name = array_shift($params);
        // table name correct.
        if (in_array($table_name, $getTables, true)) {
            CLI::write("Data of table {$table_name} : ", 'black', 'yellow');
            $thead = $db->getFieldNames($table_name);
            $tbody = $db->table($table_name)->get()->getResultArray();
            return CLI::table($tbody, $thead);
        }
                 
        $table_key = CLI::promptByKey(['These are your tables List :', 'Which table do you want see info?'], $getTables);
        CLI::write("Data of table $getTables[$table_key] : ", 'black', 'yellow');
        $thead     = $db->getFieldNames($getTables[$table_key]);
        $tbody     = $db->table($getTables[$table_key])->get()->getResultArray();

        return CLI::table($tbody, $thead);
        
            
    }
}
