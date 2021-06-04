<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourcePresenter;

class MockResourcePresenter extends ResourcePresenter
{
    use ResponseTrait;

    public function getModel()
    {
        return $this->model;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function getFormat()
    {
        return $this->format;
    }
}
