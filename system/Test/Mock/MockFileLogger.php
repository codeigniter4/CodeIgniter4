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

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Log\Handlers\FileHandler;

/**
 * Extends FileHandler, exposing some inner workings
 */
class MockFileLogger extends FileHandler
{
    /**
     * Where would the log be written?
     *
     * @var string
     */
    public $destination;

    /**
     * @param array{handles?: list<string>, path?: string, fileExtension?: string, filePermissions?: int} $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->destination = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;
    }
}
