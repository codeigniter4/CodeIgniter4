<?php namespace CodeIgniter\Commands\Sessions;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\App;

class CreateMigration extends BaseCommand
{
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'session:migration';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Generates the migration file for database sessions.';

    /**
     * Creates a new migration file with the current timestamp.
     */
    public function run(array $params=[])
    {
        $name = 'create_sessions_table';

        $path = APPPATH.'Database/Migrations/'.date('YmdHis_').$name.'.php';

        $config = new App();

        $data = [
            'matchIP'   => $config->sessionMatchIP  ?? false,
            'tableName' => $config->sessionSavePath ?? 'ci_sessions',
        ];

        $template = view('\CodeIgniter\Commands\Sessions\Views\migration.tpl', $data);
        $template = str_replace('@php', '<?php', $template);

        // Write the file out.
        helper('filesystem');
        if (! write_file($path, $template))
        {
            CLI::error(lang('Migrations.migWriteError'));
            return;
        }

        CLI::write('Created file: '. CLI::color(str_replace(APPPATH, 'APPPATH/', $path), 'green'));
    }
}
