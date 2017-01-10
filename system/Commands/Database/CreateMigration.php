<?php namespace CodeIgniter\Commands\Database;

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
