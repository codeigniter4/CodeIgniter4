<?php namespace CodeIgniter\View;

/**
 * Interface RenderableInterface
 *
 * The interface used for displaying views and/or theme files.
 *
 * @package CodeIgniter\View
 */
interface RenderableInterface {

	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * @param string $view
	 * @param array  $options  Reserved for 3rd-party uses since
	 *                         it might be needed to pass additional info
	 *                         to other template engines.
	 *
	 * @return string
	 */
	public function render(string $view, array $options=[]): string;

	//--------------------------------------------------------------------

	/**
	 * Sets several pieces of view data at once.
	 *
	 * @param array $data
	 * @param bool $escape Whether the values should be escaped
	 *
	 * @return RenderableInterface
	 */
	public function setData(array $data=[], bool $escape=true): self;

	//--------------------------------------------------------------------

	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param null   $value
	 * @param bool   $escape Whether the value should be escaped
	 *
	 * @return RenderableInterface
	 */
	public function setVar(string $name, $value=null, bool $escape=true): self;

	//--------------------------------------------------------------------

}