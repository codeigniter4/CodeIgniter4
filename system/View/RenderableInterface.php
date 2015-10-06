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
	 *
	 * @return string
	 */
	public function render(string $view, array $data=[]): string;

	//--------------------------------------------------------------------

	/**
	 * Sets several pieces of view data at once.
	 *
	 * @param array $data
	 *
	 * @return RenderableInterface
	 */
	public function setData(array $data=[]): self;

	//--------------------------------------------------------------------

	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param null   $value
	 *
	 * @return RenderableInterface
	 */
	public function setVar(string $name, $value=null): self;

	//--------------------------------------------------------------------

}