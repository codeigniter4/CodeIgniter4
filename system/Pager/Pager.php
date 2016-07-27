<?php namespace CodeIgniter\Pager;

use CodeIgniter\HTTP\URI;
use CodeIgniter\View\RenderableInterface;
use Config\Pager as PagerConfig;
use Config\Services;

class Pager implements PagerInterface
{
	/**
	 * The group data.
	 *
	 * @var array
	 */
	protected $groups = [];

	/**
	 * Our configuration instance.
	 *
	 * @var \Config\Pager
	 */
	protected $config;

	/**
	 * The view engine to render the links with.
	 *
	 * @var RenderableInterface
	 */
	protected $view;

	//--------------------------------------------------------------------

	public function __construct($config, RenderableInterface $view)
	{
		$this->config = $config;
		$this->view   = $view;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles creating and displaying the
	 *
	 * @param string|null $group
	 * @param string      $template The output template alias to render.
	 *
	 * @return string
	 */
	public function links(string $template = 'default_full', string $group = 'default'): string
	{
		$this->ensureGroup($group);
	}

	//--------------------------------------------------------------------

	/**
	 * Creates simple Next/Previous links, instead of full pagination.
	 *
	 * @param string $template
	 * @param string $group
	 *
	 * @return string
	 */
	public function simpleLinks(string $template = 'default_simple', string $group = 'default'): string
	{
		$this->ensureGroup($group);
	}

	//--------------------------------------------------------------------

	/**
	 * Allows for a simple, manual, form of pagination where all of the data
	 * is provided by the user. The URL is the current URI.
	 *
	 * @param int    $page
	 * @param int    $perPage
	 * @param int    $total
	 * @param string $template The output template alias to render.
	 *
	 * @return string
	 */
	public function makeLinks(int $page, int $perPage, int $total, string $template = 'default_full'): string
	{
		$name = time();

		$this->store($name, $page, $perPage, $total);

		$pager = new PagerRenderer($this->getDetails($name));

		if (! array_key_exists($template, $this->config->templates))
		{
			throw new \InvalidArgumentException($template.' is not a valid Pager template.');
		}

		return $this->view->setVar('pager', $pager)
						  ->render($this->config->templates[$template]);
	}

	//--------------------------------------------------------------------

	/**
	 * Stores a set of pagination data for later display. Most commonly used
	 * by the model to automate the process.
	 *
	 * @param string $group
	 * @param int    $page
	 * @param int    $perPage
	 * @param int    $total
	 *
	 * @return mixed
	 */
	public function store(string $group, int $page, int $perPage, int $total)
	{
		$this->ensureGroup($group);

		$this->groups[$group]['currentPage'] = $page;
		$this->groups[$group]['perPage']     = $perPage;
		$this->groups[$group]['total']       = $total;
		$this->groups[$group]['pageCount']   = ceil($total/$perPage);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the path that an aliased group of links will use.
	 *
	 * @param string $group
	 * @param string $path
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
	 * Returns the total number of pages.
	 *
	 * @param string|null $group
	 *
	 * @return int
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
	 * @param string|null $group
	 *
	 * @return int
	 */
	public function getCurrentPage(string $group = 'default'): int
	{
		$this->ensureGroup($group);

		return $this->groups[$group]['currentPage'];
	}

	//--------------------------------------------------------------------

	/**
	 * Tells whether this group of results has any more pages of results.
	 *
	 * @param string|null $group
	 *
	 * @return bool
	 */
	public function hasMore(string $group = 'default'): bool
	{
		$this->ensureGroup($group);

		return ($this->groups[$group]['currentPage']*$this->groups[$group]['perPage'])
			   < $this->groups[$group]['total'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last page, if we have a total that we can calculate with.
	 *
	 * @param string $group
	 *
	 * @return int|null
	 */
	public function getLastPage(string $group = 'default')
	{
		$this->ensureGroup($group);

		if (! is_numeric($this->groups[$group]['total']) || ! is_numeric($this->groups[$group]['perPage']))
		{
			return null;
		}

		return ceil($this->groups[$group]['total']/$this->groups[$group]['perPage']);
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the first page # that should be shown.
	 *
	 * @param string $group
	 *
	 * @return int
	 */
	public function getFirstPage(string $group = 'default')
	{
		$this->ensureGroup($group);

		// @todo determine based on a 'surroundCount' value
		return 1;
	}

	//--------------------------------------------------------------------


	/**
	 * Returns the URI for a specific page for the specified group.
	 *
	 * @param int    $page
	 * @param string $group
	 * @param bool   $returnObject
	 *
	 * @return string
	 */
	public function getPageURI(int $page = null, string $group = 'default', $returnObject = false)
	{
		$this->ensureGroup($group);

		$uri = $this->groups[$group]['uri'];

		$uri->setQuery('page='.$page);

		return $returnObject === true
			? $uri
			: (string)$uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the full URI to the next page of results, or null.
	 *
	 * @param string $group
	 * @param bool   $returnObject
	 *
	 * @return string|null
	 */
	public function getNextPageURI(string $group = 'default', $returnObject = false)
	{
		$this->ensureGroup($group);

		$last = $this->getLastPage($group);
		$curr = $this->getCurrentPage($group);
		$page = null;

		if (! empty($last) && ! empty($curr) && $last == $curr)
		{
			return null;
		}

		if ($last > $curr)
		{
			$page = $curr+1;
		}

		return $this->getPageURI($page, $group, $returnObject);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the full URL to the previous page of results, or null.
	 *
	 * @param string $group
	 * @param bool   $returnObject
	 *
	 * @return string|null
	 */
	public function getPreviousPageURI(string $group = 'default', $returnObject = false)
	{
		$this->ensureGroup($group);

		$first = $this->getFirstPage($group);
		$curr  = $this->getCurrentPage($group);
		$page  = null;

		if (! empty($first) && ! empty($curr) && $first == $curr)
		{
			return null;
		}

		if ($first < $curr)
		{
			$page = $curr-1;
		}

		return $this->getPageURI($page, $group, $returnObject);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of results per page that should be shown.
	 *
	 * @param string $group
	 *
	 * @return int
	 */
	public function getPerPage(string $group = 'default'): int
	{
		$this->ensureGroup($group);

		return (int)$this->groups[$group]['perPage'];
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
			throw new \InvalidArgumentException($group.' is not a valid Pagination group.');
		}

		$newGroup = $this->groups[$group];

		$newGroup['uri']      = $newGroup['uri'];
		$newGroup['next']     = $this->getNextPageURI($group);
		$newGroup['previous'] = $this->getPreviousPageURI($group);

		return $newGroup;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that an array exists for the group specified.
	 *
	 * @param string $group
	 */
	protected function ensureGroup(string $group)
	{
		if (array_key_exists($group, $this->groups))
		{
			return;
		}

		$this->groups[$group] = [
			'uri'         => clone Services::request()->uri,
			'hasMore'     => false,
			'total'       => null,
			'currentPage' => $_GET['page_'.$group] ?? $_GET['page'] ?? 1,
			'perPage'     => $this->config->perPage,
			'pageCount'   => 1,
		];
	}

	//--------------------------------------------------------------------

}
