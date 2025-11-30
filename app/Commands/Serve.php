<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Commands\Server\Serve as CoreServe;

class Serve extends CoreServe
{
    protected $group       = 'CodeIgniter';
    protected $name        = 'serve';
    protected $description = 'Start CI4 dev server with integrated HotReload.';

    public function run(array $params)
    {
        CLI::write("🚀 Iniciando servidor con HotReload integrado…", 'green');

        $this->startHotReloadWatcher();
        return parent::run($params);
    }

    private function startHotReloadWatcher()
    {
        $php = PHP_BINARY;
        $spark = realpath(ROOTPATH . 'spark');

        if (stripos(PHP_OS, 'WIN') === 0) {
            $cmd = "start /B $php $spark hotreload >nul 2>&1";
            pclose(popen($cmd, "r"));
        } else {
            shell_exec("$php $spark hotreload > /dev/null 2>&1 &");
        }

        CLI::write("🔁 HotReload watcher iniciado.", 'yellow');
    }
}