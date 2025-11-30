<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class HotReloadController extends Controller
{
    public function check()
    {
        $cacheFile = WRITEPATH . 'cache/hotreload.txt';

        if (!file_exists($cacheFile)) {
            file_put_contents($cacheFile, '0');
        }

        return $this->response->setJSON([
            'hash' => file_get_contents($cacheFile)
        ]);
    }
}
