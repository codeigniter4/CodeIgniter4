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

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Input\InputData;
use CodeIgniter\Input\InputDataFactory;

/**
 * Provides typed input access for request data sources.
 *
 * @see \CodeIgniter\HTTP\RequestInputTest
 */
final readonly class RequestInput
{
    public function __construct(
        private IncomingRequest $request,
        private InputDataFactory $factory,
    ) {
    }

    /**
     * Returns GET parameters as a typed input object.
     */
    public function get(): InputData
    {
        $data = $this->request->getGet();

        return $this->factory->create(is_array($data) ? $data : []);
    }

    /**
     * Returns POST body parameters as a typed input object.
     */
    public function post(): InputData
    {
        $data = $this->request->getPost();

        return $this->factory->create(is_array($data) ? $data : []);
    }

    /**
     * Returns JSON body parameters as a typed input object.
     */
    public function json(): InputData
    {
        $data = $this->request->getJSON(true) ?? [];

        if (! is_array($data)) {
            throw HTTPException::forUnsupportedJSONFormat();
        }

        return $this->factory->create($data);
    }

    /**
     * Returns raw input parameters as a typed input object.
     */
    public function raw(): InputData
    {
        return $this->factory->create($this->request->getRawInput());
    }
}
