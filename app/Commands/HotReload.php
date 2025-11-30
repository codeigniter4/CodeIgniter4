<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;

class HotReload extends BaseCommand
{
    protected $group       = 'Development';
    protected $name        = 'hotreload';
    protected $description = 'Monitor changes in app/Views and trigger browser reload.';

    public function run(array $params)
    {
        $watchPath = APPPATH . 'Views';
        $flagFile  = WRITEPATH . 'cache/hotreload.txt';

        if (!file_exists($flagFile)) {
            file_put_contents($flagFile, time());
        }

        echo "🚀 HotReload activo — Monitoreando: {$watchPath}\n";

        $lastHash = null;

        while (true) {
            clearstatcache();

            $currentHash = md5(json_encode($this->scanFiles($watchPath)));

            if ($lastHash !== null && $currentHash !== $lastHash) {
                file_put_contents($flagFile, time());
                echo "♻ Cambio detectado → Recargando navegador…\n";
            }

            $lastHash = $currentHash;
            sleep(1);
        }
    }

    private function scanFiles($path)
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $files = [];

        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            $files[] = [
                'file' => $file->getPathname(),
                'mtime' => $file->getMTime(),
            ];
        }

        return $files;
    }
}
