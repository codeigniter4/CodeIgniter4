<?php namespace CodeIgniter\Commands\Database;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Displays a list of all migrations and whether they've been run or not.
 *
 * @package CodeIgniter\Commands
 */
class MigrateStatus extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:status';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Displays a list of all migrations and whether they\'ve been run or not.';

    /**
     * Displays a list of all migrations and whether they've been run or not.
     */
    public function run(array $params=[])
    {
        $runner = Services::migrations();

        $migrations = $runner->findMigrations();
        $history    = $runner->getHistory();

        if (empty($migrations))
        {
            return CLI::error(lang('Migrations.migNoneFound'));
        }

        $max = 0;

        foreach ($migrations as $version => $file)
        {
            $file = substr($file, strpos($file, $version.'_'));
            $migrations[$version] = $file;

            $max = max($max, strlen($file));
        }

        CLI::write(str_pad(lang('Migrations.filename'), $max+4).lang('Migrations.migOn'), 'yellow');

        foreach ($migrations as $version => $file)
        {
            $date = '';
            foreach ($history as $row)
            {
                if ($row['version'] != $version) continue;

                $date = $row['time'];
            }

            CLI::write(str_pad($file, $max+4). ($date ? $date : '---'));
        }
    }
}
