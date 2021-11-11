<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Files;

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\Mimes;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Value object representing a single file uploaded through an
 * HTTP request. Used by the IncomingRequest class to
 * provide files.
 *
 * Typically, implementors will extend the SplFileInfo class.
 */
class UploadedFile extends File implements UploadedFileInterface
{
    /**
     * The path to the temporary file.
     *
     * @var string
     */
    protected $path;

    /**
     * The original filename as provided by the client.
     *
     * @var string
     */
    protected $originalName;

    /**
     * The filename given to a file during a move.
     *
     * @var string
     */
    protected $name;

    /**
     * The type of file as provided by PHP
     *
     * @var string
     */
    protected $originalMimeType;

    /**
     * The error constant of the upload
     * (one of PHP's UPLOADERRXXX constants)
     *
     * @var int
     */
    protected $error;

    /**
     * Whether the file has been moved already or not.
     *
     * @var bool
     */
    protected $hasMoved = false;

    /**
     * Accepts the file information as would be filled in from the $_FILES array.
     *
     * @param string $path         The temporary location of the uploaded file.
     * @param string $originalName The client-provided filename.
     * @param string $mimeType     The type of file as provided by PHP
     * @param int    $size         The size of the file, in bytes
     * @param int    $error        The error constant of the upload (one of PHP's UPLOADERRXXX constants)
     */
    public function __construct(string $path, string $originalName, ?string $mimeType = null, ?int $size = null, ?int $error = null)
    {
        $this->path             = $path;
        $this->name             = $originalName;
        $this->originalName     = $originalName;
        $this->originalMimeType = $mimeType;
        $this->size             = $size;
        $this->error            = $error;

        parent::__construct($path, false);
    }

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
     * @param string $targetPath Path to which to move the uploaded file.
     * @param string $name       the name to rename the file to.
     * @param bool   $overwrite  State for indicating whether to overwrite the previously generated file with the same
     *                           name or not.
     *
     * @throws InvalidArgumentException if the $path specified is invalid.
     * @throws RuntimeException         on any error during the move operation.
     * @throws RuntimeException         on the second or subsequent call to the method.
     *
     * @return bool
     */
    public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
    {
        $targetPath = rtrim($targetPath, '/') . '/';
        $targetPath = $this->setPath($targetPath); // set the target path

        if ($this->hasMoved) {
            throw HTTPException::forAlreadyMoved();
        }

        if (! $this->isValid()) {
            throw HTTPException::forInvalidFile();
        }

        $name        = $name ?? $this->getName();
        $destination = $overwrite ? $targetPath . $name : $this->getDestination($targetPath . $name);

        try {
            move_uploaded_file($this->path, $destination);
        } catch (Exception $e) {
            $error   = error_get_last();
            $message = isset($error['message']) ? strip_tags($error['message']) : '';

            throw HTTPException::forMoveFailed(basename($this->path), $targetPath, $message);
        }

        @chmod($targetPath, 0777 & ~umask());

        // Success, so store our new information
        $this->path     = $targetPath;
        $this->name     = basename($destination);
        $this->hasMoved = true;

        return true;
    }

    /**
     * create file target path if
     * the set path does not exist
     *
     * @return string The path set or created.
     */
    protected function setPath(string $path): string
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
            // create the index.html file
            if (! is_file($path . 'index.html')) {
                $file = fopen($path . 'index.html', 'x+b');
                fclose($file);
            }
        }

        return $path;
    }

    /**
     * Returns whether the file has been moved or not. If it has,
     * the move() method will not work and certain properties, like
     * the tempName, will no longer be available.
     */
    public function hasMoved(): bool
    {
        return $this->hasMoved;
    }

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
    public function getError(): int
    {
        return $this->error ?? UPLOAD_ERR_OK;
    }

    /**
     * Get error string
     */
    public function getErrorString(): string
    {
        $errors = [
            UPLOAD_ERR_OK         => lang('HTTP.uploadErrOk'),
            UPLOAD_ERR_INI_SIZE   => lang('HTTP.uploadErrIniSize'),
            UPLOAD_ERR_FORM_SIZE  => lang('HTTP.uploadErrFormSize'),
            UPLOAD_ERR_PARTIAL    => lang('HTTP.uploadErrPartial'),
            UPLOAD_ERR_NO_FILE    => lang('HTTP.uploadErrNoFile'),
            UPLOAD_ERR_CANT_WRITE => lang('HTTP.uploadErrCantWrite'),
            UPLOAD_ERR_NO_TMP_DIR => lang('HTTP.uploadErrNoTmpDir'),
            UPLOAD_ERR_EXTENSION  => lang('HTTP.uploadErrExtension'),
        ];

        $error = $this->error ?? UPLOAD_ERR_OK;

        return sprintf($errors[$error] ?? lang('HTTP.uploadErrUnknown'), $this->getName());
    }

    /**
     * Returns the mime type as provided by the client.
     * This is NOT a trusted value.
     * For a trusted version, use getMimeType() instead.
     *
     * @return string The media type sent by the client or null if none was provided.
     */
    public function getClientMimeType(): string
    {
        return $this->originalMimeType;
    }

    /**
     * Retrieve the filename. This will typically be the filename sent
     * by the client, and should not be trusted. If the file has been
     * moved, this will return the final name of the moved file.
     *
     * @return string The filename sent by the client or null if none was provided.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the name of the file as provided by the client during upload.
     */
    public function getClientName(): string
    {
        return $this->originalName;
    }

    /**
     * Gets the temporary filename where the file was uploaded to.
     */
    public function getTempName(): string
    {
        return $this->path;
    }

    /**
     * Overrides SPLFileInfo's to work with uploaded files, since
     * the temp file that's been uploaded doesn't have an extension.
     *
     * This method tries to guess the extension from the files mime
     * type but will return the clientExtension if it fails to do so.
     *
     * This method will always return a more or less helpfull extension
     * but might be insecure if the mime type is not machted. Consider
     * using guessExtension for a more safe version.
     */
    public function getExtension(): string
    {
        return $this->guessExtension() ?: $this->getClientExtension();
    }

    /**
     * Attempts to determine the best file extension from the file's
     * mime type. In contrast to getExtension, this method will return
     * an empty string if it fails to determine an extension instead of
     * falling back to the unsecure clientExtension.
     */
    public function guessExtension(): string
    {
        return Mimes::guessExtensionFromType($this->getMimeType(), $this->getClientExtension()) ?? '';
    }

    /**
     * Returns the original file extension, based on the file name that
     * was uploaded. This is NOT a trusted source.
     * For a trusted version, use guessExtension() instead.
     */
    public function getClientExtension(): string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION) ?? '';
    }

    /**
     * Returns whether the file was uploaded successfully, based on whether
     * it was uploaded via HTTP and has no errors.
     */
    public function isValid(): bool
    {
        return is_uploaded_file($this->path) && $this->error === UPLOAD_ERR_OK;
    }

    /**
     * Save the uploaded file to a new location.
     *
     * By default, upload files are saved in writable/uploads directory. The YYYYMMDD folder
     * and random file name will be created.
     *
     * @param string $folderName the folder name to writable/uploads directory.
     * @param string $fileName   the name to rename the file to.
     *
     * @return string file full path
     */
    public function store(?string $folderName = null, ?string $fileName = null): string
    {
        $folderName = rtrim($folderName ?? date('Ymd'), '/') . '/';
        $fileName   = $fileName ?? $this->getRandomName();

        // Move the uploaded file to a new location.
        $this->move(WRITEPATH . 'uploads/' . $folderName, $fileName);

        return $folderName . $this->name;
    }
}
