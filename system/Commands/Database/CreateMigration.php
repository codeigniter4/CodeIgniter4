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

/**
 * Creates a new migration file.
 *
 * @package CodeIgniter\Commands
 */
class CreateMigration extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:create';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Creates a new migration file.';

    /**
     * Creates a new migration file with the current timestamp.
     * @todo Have this check the settings and see what type of file it should create (timestamp or sequential)
     */
    public function run(array $params=[])
    {
        $name = array_shift($params);

        if (empty($name))
        {
            $name = CLI::prompt(lang('Migrations.migNameMigration'));
        }

        if (empty($name))
        {
            CLI::error(lang('Migrations.migBadCreateName'));
            return;
        }

        $path = APPPATH.'Database/Migrations/'.date('YmdHis_').$name.'.php';

        $template =<<<EOD
<?php

use CodeIgniter\Database\Migration;

class Migration_{name} extends Migration
{
    public function up()
    {
        //
    }
    
    //--------------------------------------------------------------------
    
    public function down()
    {
        //
    }
}

EOD;
        $template = str_replace('{name}', $name, $template);

        helper('filesystem');
        if (! write_file($path, $template))
        {
            CLI::error(lang('Migrations.migWriteError'));
            return;
        }

        CLI::write('Created file: '. CLI::color(str_replace(APPPATH, 'APPPATH/', $path), 'green'));
    }
}
