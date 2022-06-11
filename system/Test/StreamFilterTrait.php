<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Test\Filters\CITestStreamFilter;

trait StreamFilterTrait
{
    /**
     * @var resource|null
     */
    private $streamFilterOutResource;

    /**
     * @var resource|null
     */
    private $streamFilterErrResource;

    /**
     * @return $this
     */
    protected function appendStreamOutputFilter()
    {
        $this->removeStreamOutputFilter();

        $this->streamFilterOutResource = stream_filter_append(STDOUT, 'CITestStreamFilter');

        return $this;
    }

    /**
     * @return $this
     */
    protected function appendStreamErrorFilter()
    {
        $this->removeStreamErrorFilter();

        $this->streamFilterErrResource = stream_filter_append(STDERR, 'CITestStreamFilter');

        return $this;
    }

    /**
     * @return $this
     */
    protected function removeStreamOutputFilter()
    {
        if (is_resource($this->streamFilterOutResource)) {
            stream_filter_remove($this->streamFilterOutResource);
            $this->streamFilterOutResource = null;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function removeStreamErrorFilter()
    {
        if (is_resource($this->streamFilterErrResource)) {
            stream_filter_remove($this->streamFilterErrResource);
            $this->streamFilterErrResource = null;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function registerStreamFilterClass()
    {
        CITestStreamFilter::init();

        return $this;
    }

    protected function getStreamFilterBuffer(): string
    {
        return CITestStreamFilter::$buffer;
    }

    protected function resetStreamFilterBuffer(): void
    {
        CITestStreamFilter::$buffer = '';
    }
}
