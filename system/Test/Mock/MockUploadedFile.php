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

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * An uploaded file for feature tests, using a regular local file as its source.
 */
class MockUploadedFile extends UploadedFile
{
    public function __construct(
        string $path,
        string $originalName,
        ?string $mimeType = null,
        ?int $size = null,
        ?int $error = UPLOAD_ERR_OK,
        ?string $clientPath = null,
    ) {
        parent::__construct($path, $originalName, $mimeType ?? '', $size, $error, $clientPath);
    }

    public function isValid(): bool
    {
        return is_file($this->path) && $this->error === UPLOAD_ERR_OK;
    }

    protected function moveFile(string $destination): bool
    {
        return rename($this->path, $destination);
    }
}
