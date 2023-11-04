<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HotReloader;

/**
 * @internal
 */
final class HotReloader
{
    private string $refreshFile = WRITEPATH . 'cache/hot-reload';

    public function run(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        ini_set('zlib.output_compression', 'Off');

        header('Cache-Control: no-store');
        header('Content-Type: text/event-stream');
        header('Access-Control-Allow-Methods: GET');

        ob_end_clean();

        if (connection_status() !== CONNECTION_NORMAL || connection_aborted()) {
            if (is_file($this->refreshFile)) {
                unlink($this->refreshFile);
            }

            return;
        }

        $appHash = '';
        $hasher  = new DirectoryHasher();

        $currentHash = $hasher->hash();

        if (is_file($this->refreshFile)) {
            $appHash = (string) file_get_contents($this->refreshFile);
        }

        if ($appHash === '') {
            $appHash = $currentHash;
            file_put_contents($this->refreshFile, $currentHash);
        }

        // If hash has changed, tell the browser to reload.
        if ($currentHash !== $appHash) {
            $this->sendEvent('reload', ['time' => date('Y-m-d H:i:s')]);

            file_put_contents($this->refreshFile, $currentHash);

            return;
        }

        $this->sendEvent('ping', ['time' => date('Y-m-d H:i:s')]);
        sleep(1);
    }

    /**
     * Send an event to the browser.
     */
    private function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";

        ob_flush();
        flush();
    }
}
