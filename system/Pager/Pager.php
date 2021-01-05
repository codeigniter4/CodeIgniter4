<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Pager;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Pager\Exceptions\PagerException;
use CodeIgniter\View\RendererInterface;
use Config\Pager as PagerConfig;

/**
 * Class Pager
 *
 * The Pager class provides semi-automatic and manual methods for creating
 * pagination links and reading the current url's query variable, "page"
 * to determine the current page. This class can support multiple
 * paginations on a single page.
 */
class Pager implements PagerInterface
{
	/**
	 * The group data.
	 *
	 * @var array
	 */
	protected $groups = [];

	/**
	 * URI segment for groups if provided.
	 *
	 * @var array
	 */
	protected $segment = [];

	/**
	 * Our configuration instance.
	 *
	 * @var PagerConfig
	 */
	protected $config;

	/**
	 * The view engine to render the links with.
	 *
	 * @var RendererInterface
	 */
	protected $view;

	/**
	 * List of only permitted queries
	 *
	 * @var array
	 */
	protected $only = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param PagerConfig       $config
	 * @param RendererInterface $view
	 */
	public function __construct(PagerConfig $config, RendererInterface $view)
	{
		$this->config = $config;
		$this->view   = $view;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles creating and displaying the
	 *
	 * @param string $group
	 * @param string $template The output template alias to render.
	 *
	 * @return string
	 */
	public function links(string $group = 'default', string $template = 'default_full'): string
	{
		$this->ensureGroup($group);

		return $this->displayLinks($group, $template);
	}

	//--------------------------------------------------------------------

	/**
	 * Creates simple Next/Previous links, instead of full pagination.
	 *
	 * @param string $group
	 * @param string $template
	 *
	 * @return string
	 */
	public function simpleLinks(string $group = 'default', string $template = 'default_simple'): string
	{
		$this->ensureGroup($group);

		return $this->displayLinks($group, $template);
	}

	//--------------------------------------------------------------------

	/**
	 * Allows for a simple, manual, form of pagination where all of the data
	 * is provided by the user. The URL is the current URI.
	 *
	 * @param integer      $page
	 * @param integer|null $perPage
	 * @param integer      $total
	 * @param string       $template The output template alias to render.
	 * @param integer      $segment  (whether page number is provided by URI segment)
	 * @param string|null  $group    optional group (i.e. if we'd like to define custom path)
	 *
	 * @return string
	 */
	public function makeLinks(int $page, ?int $perPage, int $total, string $template = 'default_full', int $segment = 0, ?string $group = 'default'): string
	{
		$group = $group === '' ? 'default' : $group;

		$this->store($group, $page, $perPage ?? $this->config->perPage, $total, $segment);

		return $this->displayLinks($group, $template);
	}

	//--------------------------------------------------------------------

	/**
	 * Does the actual work of displaying the view file. Used internally
	 * by links(), simpleLinks(), and makeLinks().
	 *
	 * @param string $group
	 * @param string $template
	 *
	 * @return string
	 */
	protected function displayLinks(string $group, string $template): string
	{
		$pager = new PagerRenderer($this->getDetails($group));

		if (! array_key_exists($template, $this->config->templates))
		{
			throw PagerException::forInvalidTemplate($template);
		}

		return $this->view->setVar('pager', $pager)
						->render($this->config->templates[$template]);
	}

	//--------------------------------------------------------------------

	/**
	 * Stores a set of pagination data for later display. Most commonly used
	 * by the model to automate the process.
	 *
	 * @param string       $group
	 * @param integer      $page
	 * @param integer|null $perPage
	 * @param integer      $total
	 * @param integer      $segment
	 *
	 * @return $this
	 */
	public function store(string $group, int $page, ?int $perPage, int $total, int $segment = 0)
	{
		if ($segment)
		{
			$this->setSegment($segment, $group);
		}

		$this->ensureGroup($group, $perPage);

		if ($segment > 0 && $this->groups[$group]['currentPage'] > 0)
		{
			$page = $this->groups[$group]['currentPage'];
		}

		$perPage   = $perPage ?? $this->config->perPage;
		$pageCount = (int) ceil($total / $perPage);

		$this->groups[$group]['currentPage'] = $page > $pageCount ? $pageCount : $page;
		$this->groups[$group]['perPage']     = $perPage;
		$this->groups[$group]['total']       = $total;
		$this->groups[$group]['pageCount']   = $pageCount;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets segment for a group.
	 *
	 * @param integer $number
	 * @param string  $group
	 *
	 * @return mixed
	 */
	public function setSegment(int $number, string $group = 'default')
	{
		$this->segment[$group] = $number;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the path that an aliased group of links will use.
	 *
	 * @param string $path
	 * @param string $group
	 *
	 * @return mixed
	 */
	public function setPath(string $path, string $group = 'default')
	{
		$this->ensureGroup($group);

		$this->groups[$group]['uri']->setPath($path);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of items in data store.
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getTotal(string $group = 'default'): int
	{
		$this->ensureGroup($group);

		return $this->groups[$group]['total'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of pages.
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getPageCount(string $group = 'default'): int
	{
		$this->ensureGroup($group);

		return $this->groups[$group]['pageCount'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of the current page of results.
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getCurrentPage(string $group = 'default'): int
	{
		$this->ensureGroup($group);

		return $this->groups[$group]['currentPage'] ?: 1;
	}

	//--------------------------------------------------------------------

	/**
	 * Tells whether this group of results has any more pages of results.
	 *
	 * @param string $group
	 *
	 * @return boolean
	 */
	public function hasMore(string $group = 'default'): bool
	{
		$this->ensureGroup($group);

		return ($this->groups[$group]['currentPage'] * $this->groups[$group]['perPage']) < $this->groups[$group]['total'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last page, if we have a total that we can calculate with.
	 *
	 * @param string $group
	 *
	 * @return integer|null
	 */
	public function getLastPage(string $group = 'default')
	{
		$this->ensureGroup($group);

		if (! is_numeric($this->groups[$group]['total']) || ! is_numeric($this->groups[$group]['perPage']))
		{
			return null;
		}

		return (int) ceil($this->groups[$group]['total'] / $this->groups[$group]['perPage']);
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the first page # that should be shown.
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getFirstPage(string $group = 'default'): int
	{
		$this->ensureGroup($group);

		// @todo determine based on a 'surroundCount' value
		return 1;
	}

	//--------------------------------------------------------------------
	/**
	 * Returns the URI for a specific page for the specified group.
	 *
	 * @param integer|null $page
	 * @param string       $group
	 * @param boolean      $returnObject
	 *
	 * @return string|URI
	 */
	public function getPageURI(int $page = null, string $group = 'default', bool $returnObject = false)
	{
		$this->ensureGroup($group);

		/**
		 * @var URI $uri
		 */
		$uri = $this->groups[$group]['uri'];

		$segment = $this->segment[$group] ?? 0;

		if ($segment)
		{
			$uri->setSegment($segment, $page);
		}
		else
		{
			$uri->addQuery($this->groups[$group]['pageSelector'], $page);
		}

		if ($this->only)
		{
			$query = array_intersect_key($_GET, array_flip($this->only));

			if (! $segment)
			{
				$query[$this->groups[$group]['pageSelector']] = $page;
			}

			$uri->setQueryArray($query);
		}

		return $returnObject === true ? $uri : (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the full URI to the next page of results, or null.
	 *
	 * @param string  $group
	 * @param boolean $returnObject
	 *
	 * @return string|null
	 */
	public function getNextPageURI(string $group = 'default', bool $returnObject = false)
	{
		$this->ensureGroup($group);

		$last = $this->getLastPage($group);
		$curr = $this->getCurrentPage($group);
		$page = null;

		if (! empty($last) && ! empty($curr) && $last === $curr)
		{
			return null;
		}

		if ($last > $curr)
		{
			$page = $curr + 1;
		}

		return $this->getPageURI($page, $group, $returnObject);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the full URL to the previous page of results, or null.
	 *
	 * @param string  $group
	 * @param boolean $returnObject
	 *
	 * @return string|null
	 */
	public function getPreviousPageURI(string $group = 'default', bool $returnObject = false)
	{
		$this->ensureGroup($group);

		$first = $this->getFirstPage($group);
		$curr  = $this->getCurrentPage($group);
		$page  = null;

		if (! empty($first) && ! empty($curr) && $first === $curr)
		{
			return null;
		}

		if ($first < $curr)
		{
			$page = $curr - 1;
		}

		return $this->getPageURI($page, $group, $returnObject);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of results per page that should be shown.
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getPerPage(string $group = 'default'): int
	{
		$this->ensureGroup($group);

		return (int) $this->groups[$group]['perPage'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array with details about the results, including
	 * total, per_page, current_page, last_page, next_url, prev_url, from, to.
	 * Does not include the actual data. This data is suitable for adding
	 * a 'data' object to with the result set and converting to JSON.
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function getDetails(string $group = 'default'): array
	{
		if (! array_key_exists($group, $this->groups))
		{
			throw PagerException::forInvalidPaginationGroup($group);
		}

		$newGroup = $this->groups[$group];

		$newGroup['next']     = $this->getNextPageURI($group);
		$newGroup['previous'] = $this->getPreviousPageURI($group);
		$newGroup['segment']  = $this->segment[$group] ?? 0;

		return $newGroup;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets only allowed queries on pagination links.
	 *
	 * @param array $queries
	 *
	 * @return Pager
	 */
	public function only(array $queries):Pager
	{
		$this->only = $queries;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that an array exists for the group specified.
	 *
	 * @param string  $group
	 * @param integer $perPage
	 */
	protected function ensureGroup(string $group, int $perPage = null)
	{
		if (array_key_exists($group, $this->groups))
		{
			return;
		}

		$this->groups[$group] = [
			'uri'          => clone current_url(true),
			'hasMore'      => false,
			'total'        => null,
			'perPage'      => $perPage ?? $this->config->perPage,
			'pageCount'    => 1,
			'pageSelector' => $group === 'default' ? 'page' : 'page_' . $group,
		];

		$this->calculateCurrentPage($group);

		if ($_GET)
		{
			$this->groups[$group]['uri'] = $this->groups[$group]['uri']->setQueryArray($_GET);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Calculating the current page
	 *
	 * @param string $group
	 */
	protected function calculateCurrentPage(string $group)
	{
		if (array_key_exists($group, $this->segment))
		{
			try
			{
				$this->groups[$group]['currentPage'] = (int) $this->groups[$group]['uri']->setSilent(false)->getSegment($this->segment[$group]);
			}
			catch (HTTPException $e)
			{
				$this->groups[$group]['currentPage'] = 1;
			}
		}
		else
		{
			$pageSelector = $this->groups[$group]['pageSelector'];

			$page = (int) ($_GET[$pageSelector] ?? 1);

			$this->groups[$group]['currentPage'] = $page < 1 ? 1 : $page;
		}
	}

	//--------------------------------------------------------------------
}
