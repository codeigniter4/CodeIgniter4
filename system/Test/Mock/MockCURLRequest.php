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

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\URI;

/**
 * Simply allows us to not actually call cURL during the
 * test runs. Instead, we can set the desired output
 * and get back the set options.
 */
class MockCURLRequest extends CURLRequest
{
    /**
     * @var array<int, mixed>
     */
    public $curl_options;

    /**
     * @var string
     */
    protected $output = '';

    /**
     * @var list<string>
     */
    protected array $outputs = [];

    /**
     * @var list<array{0: int, 1: string}>
     */
    protected array $curlErrors = [];

    /**
     * @var list<float>
     */
    protected array $sleeps = [];

    /**
     * @param string $output
     *
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param list<string> $outputs
     *
     * @return $this
     */
    public function setOutputs(array $outputs)
    {
        $this->outputs = $outputs;

        return $this;
    }

    /**
     * @param list<array{0: int, 1: string}> $curlErrors
     *
     * @return $this
     */
    public function setCurlErrors(array $curlErrors)
    {
        $this->curlErrors = $curlErrors;

        return $this;
    }

    /**
     * @param array<int, mixed> $curlOptions
     */
    protected function sendRequest(array $curlOptions = []): string
    {
        $this->response = clone $this->responseOrig;

        $this->curl_options = $curlOptions;

        if ($this->curlErrors !== []) {
            [$this->lastCurlError, $message] = array_shift($this->curlErrors);

            throw HTTPException::forCurlError((string) $this->lastCurlError, $message);
        }

        return $this->outputs !== [] ? array_shift($this->outputs) : $this->output;
    }

    protected function sleep(float $seconds): void
    {
        $this->sleeps[] = $seconds;
    }

    /**
     * for testing purposes only
     *
     * @return list<float>
     */
    public function getSleeps(): array
    {
        return $this->sleeps;
    }

    /**
     * for testing purposes only
     *
     * @return URI
     */
    public function getBaseURI()
    {
        return $this->baseURI;
    }

    /**
     * for testing purposes only
     *
     * @return float
     */
    public function getDelay()
    {
        return $this->delay;
    }
}
