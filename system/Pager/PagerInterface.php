<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Pager;

use CodeIgniter\HTTP\URI;

/**
 * Expected behavior for a Pager
 */
interface PagerInterface
{
    /**
     * Handles creating and displaying the
     *
     * @param string $template The output template alias to render.
     */
    public function links(string $group = 'default', string $template = 'default'): string;

    /**
     * Creates simple Next/Previous links, instead of full pagination.
     */
    public function simpleLinks(string $group = 'default', string $template = 'default'): string;

    /**
     * Allows for a simple, manual, form of pagination where all of the data
     * is provided by the user. The URL is the current URI.
     *
     * @param string $template The output template alias to render.
     */
    public function makeLinks(int $page, int $perPage, int $total, string $template = 'default'): string;

    /**
     * Stores a set of pagination data for later display. Most commonly used
     * by the model to automate the process.
     *
     * @return $this
     */
    public function store(string $group, int $page, int $perPage, int $total);

    /**
     * Sets the path that an aliased group of links will use.
     *
     * @return $this
     */
    public function setPath(string $path, string $group = 'default');

    /**
     * Returns the total number of pages.
     */
    public function getPageCount(string $group = 'default'): int;

    /**
     * Returns the number of the current page of results.
     */
    public function getCurrentPage(string $group = 'default'): int;

    /**
     * Returns the URI for a specific page for the specified group.
     *
     * @return string|URI
     */
    public function getPageURI(?int $page = null, string $group = 'default', bool $returnObject = false);

    /**
     * Tells whether this group of results has any more pages of results.
     */
    public function hasMore(string $group = 'default'): bool;

    /**
     * Returns the first page.
     *
     * @return int
     */
    public function getFirstPage(string $group = 'default');

    /**
     * Returns the last page, if we have a total that we can calculate with.
     *
     * @return int|null
     */
    public function getLastPage(string $group = 'default');

    /**
     * Returns the full URI to the next page of results, or null.
     *
     * @return string|null
     */
    public function getNextPageURI(string $group = 'default');

    /**
     * Returns the full URL to the previous page of results, or null.
     *
     * @return string|null
     */
    public function getPreviousPageURI(string $group = 'default');

    /**
     * Returns the number of results per page that should be shown.
     */
    public function getPerPage(string $group = 'default'): int;

    /**
     * Returns an array with details about the results, including
     * total, per_page, current_page, last_page, next_url, prev_url, from, to.
     * Does not include the actual data. This data is suitable for adding
     * a 'data' object to with the result set and converting to JSON.
     */
    public function getDetails(string $group = 'default'): array;
}
