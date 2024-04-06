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

namespace CodeIgniter\HTTP\Files;

use InvalidArgumentException;
use RuntimeException;

/**
 * Value object representing a single file uploaded through an
 * HTTP request. Used by the IncomingRequest class to
 * provide files.
 *
 * Typically, implementors will extend the SplFileInfo class.
 */
interface UploadedFileInterface
{
    /**
     * Accepts the file information as would be filled in from the $_FILES array.
     *
     * @param string      $path         The temporary location of the uploaded file.
     * @param string      $originalName The client-provided filename.
     * @param string|null $mimeType     The type of file as provided by PHP
     * @param int|null    $size         The size of the file, in bytes
     * @param int|null    $error        The error constant of the upload (one of PHP's UPLOADERRXXX constants)
     * @param string|null $clientPath   The webkit relative path of the uploaded file.
     */
    public function __construct(string $path, string $originalName, ?string $mimeType = null, ?int $size = null, ?int $error = null, ?string $clientPath = null);

    /**
     * Move the uploaded file to a new location.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     *
     * @param string      $targetPath Path to which to move the uploaded file.
     * @param string|null $name       the name to rename the file to.
     *
     * @return bool
     *
     * @throws InvalidArgumentException if the $path specified is invalid.
     * @throws RuntimeException         on the second or subsequent call to the method.
     */
    public function move(string $targetPath, ?string $name = null);

    /**
     * Returns whether the file has been moved or not. If it has,
     * the move() method will not work and certain properties, like
     * the tempName, will no longer be available.
     */
    public function hasMoved(): bool;

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see    http://php.net/manual/en/features.file-upload.errors.php
     *
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError(): int;

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string The filename sent by the client or null if none
     *                was provided.
     */
    public function getName(): string;

    /**
     * Gets the temporary filename where the file was uploaded to.
     */
    public function getTempName(): string;

    /**
     * (PHP 8.1+)
     * Returns the webkit relative path of the uploaded file on directory uploads.
     */
    public function getClientPath(): ?string;

    /**
     * Returns the original file extension, based on the file name that
     * was uploaded. This is NOT a trusted source.
     * For a trusted version, use guessExtension() instead.
     */
    public function getClientExtension(): string;

    /**
     * Returns the mime type as provided by the client.
     * This is NOT a trusted value.
     * For a trusted version, use getMimeType() instead.
     */
    public function getClientMimeType(): string;

    /**
     * Returns whether the file was uploaded successfully, based on whether
     * it was uploaded via HTTP and has no errors.
     */
    public function isValid(): bool;

    /**
     * Returns the destination path for the move operation where overwriting is not expected.
     *
     * First, it checks whether the delimiter is present in the filename, if it is, then it checks whether the
     * last element is an integer as there may be cases that the delimiter may be present in the filename.
     * For the all other cases, it appends an integer starting from zero before the file's extension.
     */
    public function getDestination(string $destination, string $delimiter = '_', int $i = 0): string;
}
