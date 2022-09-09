<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Exceptions\DownloadException;
use CodeIgniter\Files\File;
use Config\Mimes;

/**
 * HTTP response when a download is requested.
 */
class DownloadResponse extends Response
{
    /**
     * Download file name
     */
    private string $filename;

    /**
     * Download for file
     */
    private ?File $file = null;

    /**
     * mime set flag
     */
    private bool $setMime;

    /**
     * Download for binary
     */
    private ?string $binary = null;

    /**
     * Download charset
     */
    private string $charset = 'UTF-8';

    /**
     * Download reason
     *
     * @var string
     */
    protected $reason = 'OK';

    /**
     * The current status code for this response.
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Constructor.
     */
    public function __construct(string $filename, bool $setMime)
    {
        parent::__construct(config('App'));

        $this->filename = $filename;
        $this->setMime  = $setMime;

        // Make sure the content type is either specified or detected
        $this->removeHeader('Content-Type');
    }

    /**
     * set download for binary string.
     */
    public function setBinary(string $binary)
    {
        if ($this->file !== null) {
            throw DownloadException::forCannotSetBinary();
        }

        $this->binary = $binary;
    }

    /**
     * set download for file.
     */
    public function setFilePath(string $filepath)
    {
        if ($this->binary !== null) {
            throw DownloadException::forCannotSetFilePath($filepath);
        }

        $this->file = new File($filepath, true);
    }

    /**
     * set name for the download.
     *
     * @return $this
     */
    public function setFileName(string $filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * get content length.
     */
    public function getContentLength(): int
    {
        if (is_string($this->binary)) {
            return strlen($this->binary);
        }

        if ($this->file instanceof File) {
            return $this->file->getSize();
        }

        return 0;
    }

    /**
     * Set content type by guessing mime type from file extension
     */
    private function setContentTypeByMimeType()
    {
        $mime    = null;
        $charset = '';

        if ($this->setMime === true && ($lastDotPosition = strrpos($this->filename, '.')) !== false) {
            $mime    = Mimes::guessTypeFromExtension(substr($this->filename, $lastDotPosition + 1));
            $charset = $this->charset;
        }

        if (! is_string($mime)) {
            // Set the default MIME type to send
            $mime    = 'application/octet-stream';
            $charset = '';
        }

        $this->setContentType($mime, $charset);
    }

    /**
     * get download filename.
     */
    private function getDownloadFileName(): string
    {
        $filename  = $this->filename;
        $x         = explode('.', $this->filename);
        $extension = end($x);

        /* It was reported that browsers on Android 2.1 (and possibly older as well)
         * need to have the filename extension upper-cased in order to be able to
         * download it.
         *
         * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
         */
        // @todo: depend super global
        if (count($x) !== 1 && isset($_SERVER['HTTP_USER_AGENT'])
                && preg_match('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT'])) {
            $x[count($x) - 1] = strtoupper($extension);
            $filename         = implode('.', $x);
        }

        return $filename;
    }

    /**
     * get Content-Disposition Header string.
     */
    private function getContentDisposition(): string
    {
        $downloadFilename = $this->getDownloadFileName();

        $utf8Filename = $downloadFilename;

        if (strtoupper($this->charset) !== 'UTF-8') {
            $utf8Filename = mb_convert_encoding($downloadFilename, 'UTF-8', $this->charset);
        }

        $result = sprintf('attachment; filename="%s"', $downloadFilename);

        if ($utf8Filename) {
            $result .= '; filename*=UTF-8\'\'' . rawurlencode($utf8Filename);
        }

        return $result;
    }

    /**
     * Disallows status changing.
     *
     * @throws DownloadException
     */
    public function setStatusCode(int $code, string $reason = '')
    {
        throw DownloadException::forCannotSetStatusCode($code, $reason);
    }

    /**
     * Sets the Content Type header for this response with the mime type
     * and, optionally, the charset.
     *
     * @return ResponseInterface
     */
    public function setContentType(string $mime, string $charset = 'UTF-8')
    {
        parent::setContentType($mime, $charset);

        if ($charset !== '') {
            $this->charset = $charset;
        }

        return $this;
    }

    /**
     * Sets the appropriate headers to ensure this response
     * is not cached by the browsers.
     */
    public function noCache(): self
    {
        $this->removeHeader('Cache-control');

        $this->setHeader('Cache-control', ['private', 'no-transform', 'no-store', 'must-revalidate']);

        return $this;
    }

    /**
     * Disables cache configuration.
     *
     * @throws DownloadException
     */
    public function setCache(array $options = [])
    {
        throw DownloadException::forCannotSetCache();
    }

    /**
     * {@inheritDoc}
     *
     * @return $this
     *
     * @todo Do downloads need CSP or Cookies? Compare with ResponseTrait::send()
     */
    public function send()
    {
        $this->buildHeaders();
        $this->sendHeaders();
        $this->sendBody();

        return $this;
    }

    /**
     * set header for file download.
     */
    public function buildHeaders()
    {
        if (! $this->hasHeader('Content-Type')) {
            $this->setContentTypeByMimeType();
        }

        $this->setHeader('Content-Disposition', $this->getContentDisposition());
        $this->setHeader('Expires-Disposition', '0');
        $this->setHeader('Content-Transfer-Encoding', 'binary');
        $this->setHeader('Content-Length', (string) $this->getContentLength());
        $this->noCache();
    }

    /**
     * output download file text.
     *
     * @return DownloadResponse
     *
     * @throws DownloadException
     */
    public function sendBody()
    {
        if ($this->binary !== null) {
            return $this->sendBodyByBinary();
        }

        if ($this->file !== null) {
            return $this->sendBodyByFilePath();
        }

        throw DownloadException::forNotFoundDownloadSource();
    }

    /**
     * output download text by file.
     *
     * @return DownloadResponse
     */
    private function sendBodyByFilePath()
    {
        $splFileObject = $this->file->openFile('rb');

        // Flush 1MB chunks of data
        while (! $splFileObject->eof() && ($data = $splFileObject->fread(1_048_576)) !== false) {
            echo $data;
            unset($data);
        }

        return $this;
    }

    /**
     * output download text by binary
     *
     * @return DownloadResponse
     */
    private function sendBodyByBinary()
    {
        echo $this->binary;

        return $this;
    }
}
