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

namespace CodeIgniter\Commands\Cache;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Table;
use CodeIgniter\I18n\Time;
use Config\Cache;

/**
 * Shows file cache information in the current system.
 */
#[Command(name: 'cache:info', description: 'Shows file cache information in the current system.', group: 'Cache')]
class InfoCache extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $config = config(Cache::class);
        $output = $this->getOutput();

        if ($config->handler !== 'file') {
            $output->error(sprintf(
                'This command only supports the file cache handler. The configured handler is "%s".',
                $config->handler,
            ));

            return EXIT_ERROR;
        }

        $cache = service('cache', $config);
        $tbody = [];

        helper('number');

        foreach ($cache->getCacheInfo() as $key => $field) {
            $tbody[] = [
                $key,
                clean_path($field['server_path']),
                number_to_size($field['size']),
                Time::createFromTimestamp($field['date']),
            ];
        }

        $output->write((new Table($tbody, [
            $output->color('Name', 'green'),
            $output->color('Server Path', 'green'),
            $output->color('Size', 'green'),
            $output->color('Date', 'green'),
        ]))->render());

        return EXIT_SUCCESS;
    }
}
