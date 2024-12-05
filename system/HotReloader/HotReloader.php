<?php

declare(strict_types=1);

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
        set_time_limit(0);

        $hasher  = new DirectoryHasher();
        $appHash = $hasher->hash();

        while (true) {
            if (connection_status() !== CONNECTION_NORMAL || connection_aborted() === 1) {
                break;
            }

            $currentHash = $hasher->hash();

            // If hash has changed, tell the browser to reload.
            if ($currentHash !== $appHash) {
                $appHash = $currentHash;

                $this->sendEvent('reload', ['time' => date('Y-m-d H:i:s')]);
                break;
            }

            if (mt_rand(1, 10) > 8) {
                $this->sendEvent('ping', ['time' => date('Y-m-d H:i:s')]);
            }

            sleep(1);
        }
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
