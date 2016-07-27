<?php namespace CodeIgniter\Pager;

class PagerRenderer
{
	protected $first;
	protected $last;
	protected $current;
	protected $total;
	protected $pageCount;
	protected $uri;

	//--------------------------------------------------------------------

	public function __construct(array $details)
	{
		$this->first     = 1;
		$this->last      = $details['pageCount'];
		$this->current   = $details['currentPage'];
		$this->total     = $details['total'];
		$this->uri       = $details['uri'];
		$this->pageCount = $details['pageCount'];
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the total number of links that should appear on either
	 * side of the current page. Adjusts the first and last counts
	 * to reflect it.
	 *
	 * @param int $count
	 *
	 * @return $this
	 */
	public function setSurroundCount(int $count)
	{
		$this->updatePages($count);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if there is a "previous" page before our "first" page.
	 *
	 * @return bool
	 */
	public function hasPrevious(): bool
	{
		return $this->first > 1;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a URL to the "previous" page. The previous page is NOT the
	 * page before the current page, but is the page just before the
	 * "first" page.
	 *
	 * You MUST call hasPrevious() first, or this value may be invalid.
	 *
	 * @return string
	 */
	public function getPrevious(): string
	{
		$uri = clone $this->uri;

		$uri->addQuery('page', $this->first-1);

		return (string)$uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if there is a "next" page after our "last" page.
	 *
	 * @return bool
	 */
	public function hasNext(): bool
	{
		return $this->pageCount > $this->last;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a URL to the "next" page. The next page is NOT, the
	 * page after the current page, but is the page that follows the
	 * "last" page.
	 *
	 * You MUST call hasNext() first, or this value may be invalid.
	 *
	 * @return string
	 */
	public function getNext(): string
	{
		$uri = clone $this->uri;

		$uri->addQuery('page', $this->last+1);

		return (string)$uri;
	}

	//--------------------------------------------------------------------

	public function links(): array
	{
		$links = [];

		$uri = clone $this->uri;

		for ($i=$this->first; $i <= $this->last; $i++)
		{
			$links[] = [
				'uri' => (string)$uri->addQuery('page', $i),
				'title' => (int)$i,
				'active' => ($i == $this->current)
			];
		}

		return $links;
	}

	//--------------------------------------------------------------------

	/**
	 * Updates the first and last pages based on $surroundCount,
	 * which is the number of links surrounding the active page
	 * to show.
	 *
	 * @param int|null $count
	 */
	protected function updatePages(int $count = null)
	{
		if (empty($count))
		{
			return;
		}

		$this->first = $this->current-$count > 0
			? (int)($this->current-$count)
			: 1;
		$this->last  = $this->current+$count <= $this->pageCount
			? (int)($this->current+$count)
			: (int)$this->pageCount;
	}

	//--------------------------------------------------------------------

}
