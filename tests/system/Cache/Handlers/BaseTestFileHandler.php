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

namespace CodeIgniter\Cache\Handlers;

use Config\Cache;

/**
 * @internal
 */
final class BaseTestFileHandler extends FileHandler
{
    private static string $directory = 'FileHandler';
    private readonly Cache $config;

    public function __construct()
    {
        $this->config = new Cache();
        $this->config->file['storePath'] .= self::$directory;

        parent::__construct($this->config);

        helper('filesystem');
    }

    /**
     * @return array{
     *  name: string,
     *  server_path: string,
     *  size: int,
     *  date: int,
     *  readable: bool,
     *  writable: bool,
     *  executable: bool,
     *  fileperms: int,
     * }|null
     */
    public function getFileInfoTest(): ?array
    {
        $tmpHandle = tmpfile();
        stream_get_meta_data($tmpHandle);

        return get_file_info(stream_get_meta_data($tmpHandle)['uri'], [
            'name',
            'server_path',
            'size',
            'date',
            'readable',
            'writable',
            'executable',
            'fileperms',
        ]);
    }
}
