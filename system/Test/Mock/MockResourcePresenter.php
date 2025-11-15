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

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourcePresenter;

class MockResourcePresenter extends ResourcePresenter
{
    use ResponseTrait;

    /**
     * @return object|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return class-string|null
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @return 'json'|'xml'|null
     */
    public function getFormat()
    {
        return $this->format;
    }
}
