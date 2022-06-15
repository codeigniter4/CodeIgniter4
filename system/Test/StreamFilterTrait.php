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
    private $outputStreamFilterResource;

    /**
     * @var resource|null
     */
    private $errorStreamFilterResource;

    protected function setUpStreamFilterTrait(): void
    {
        $this->registerStreamFilterClass()
            ->appendOutputStreamFilter()
            ->appendErrorStreamFilter();
    }

    protected function tearDownStreamFilterTrait(): void
    {
        $this->removeOutputStreamFilter()->removeErrorStreamFilter();
    }

    /**
     * @return $this
     */
    protected function appendOutputStreamFilter()
    {
        $this->removeOutputStreamFilter();

        $this->outputStreamFilterResource = stream_filter_append(STDOUT, 'CITestStreamFilter');

        return $this;
    }

    /**
     * @return $this
     */
    protected function appendErrorStreamFilter()
    {
        $this->removeErrorStreamFilter();

        $this->errorStreamFilterResource = stream_filter_append(STDERR, 'CITestStreamFilter');

        return $this;
    }

    /**
     * @return $this
     */
    protected function removeOutputStreamFilter()
    {
        if (is_resource($this->outputStreamFilterResource)) {
            stream_filter_remove($this->outputStreamFilterResource);
            $this->outputStreamFilterResource = null;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function removeErrorStreamFilter()
    {
        if (is_resource($this->errorStreamFilterResource)) {
            stream_filter_remove($this->errorStreamFilterResource);
            $this->errorStreamFilterResource = null;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function registerStreamFilterClass()
    {
        CITestStreamFilter::registration();

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
