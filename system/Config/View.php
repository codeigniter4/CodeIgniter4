<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Config;

/**
 * View configuration
 */
class View extends BaseConfig
{

	/**
	 * Built-in View filters.
	 *
	 * @var type
	 */
	protected $coreFilters = [
		'abs'            => '\abs',
		'capitalize'     => '\CodeIgniter\View\Filters::capitalize',
		'date'           => '\CodeIgniter\View\Filters::date',
		'date_modify'    => '\CodeIgniter\View\Filters::date_modify',
		'default'        => '\CodeIgniter\View\Filters::default',
		'esc'            => '\CodeIgniter\View\Filters::esc',
		'excerpt'        => '\CodeIgniter\View\Filters::excerpt',
		'highlight'      => '\CodeIgniter\View\Filters::highlight',
		'highlight_code' => '\CodeIgniter\View\Filters::highlight_code',
		'limit_words'    => '\CodeIgniter\View\Filters::limit_words',
		'limit_chars'    => '\CodeIgniter\View\Filters::limit_chars',
		'local_currency' => '\CodeIgniter\View\Filters::local_currency',
		'local_number'   => '\CodeIgniter\View\Filters::local_number',
		'lower'          => '\strtolower',
		'nl2br'          => '\CodeIgniter\View\Filters::nl2br',
		'number_format'  => '\number_format',
		'prose'          => '\CodeIgniter\View\Filters::prose',
		'round'          => '\CodeIgniter\View\Filters::round',
		'strip_tags'     => '\strip_tags',
		'title'          => '\CodeIgniter\View\Filters::title',
		'upper'          => '\strtoupper',
	];

	/**
	 * Built-in View plugins.
	 *
	 * @var type
	 */
	protected $corePlugins = [
		'current_url'       => '\CodeIgniter\View\Plugins::currentURL',
		'previous_url'      => '\CodeIgniter\View\Plugins::previousURL',
		'mailto'            => '\CodeIgniter\View\Plugins::mailto',
		'safe_mailto'       => '\CodeIgniter\View\Plugins::safeMailto',
		'lang'              => '\CodeIgniter\View\Plugins::lang',
		'validation_errors' => '\CodeIgniter\View\Plugins::validationErrors',
		'route'             => '\CodeIgniter\View\Plugins::route',
		'siteURL'           => '\CodeIgniter\View\Plugins::siteURL',
	];

	/**
	 * Constructor.
	 *
	 * Merge the built-in and developer-configured filters and plugins,
	 * with preference to the developer ones.
	 */
	public function __construct()
	{
		$this->filters = array_merge($this->coreFilters, $this->filters);
		$this->plugins = array_merge($this->corePlugins, $this->plugins);

		parent::__construct();
	}

}
