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

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Language\Language;

class MockLanguage extends Language
{

	/**
	 * Stores the data that should be
	 * returned by the 'requireFile()' method.
	 *
	 * @var mixed
	 */
	protected $data;

	//--------------------------------------------------------------------

	/**
	 * Sets the data that should be returned by the
	 * 'requireFile()' method to allow easy overrides
	 * during testing.
	 *
	 * @param array       $data
	 * @param string      $file
	 * @param string|null $locale
	 *
	 * @return $this
	 */
	public function setData(string $file, array $data, string $locale = null)
	{
		$this->language[$locale ?? $this->locale][$file] = $data;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides an override that allows us to set custom
	 * data to be returned easily during testing.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	protected function requireFile(string $path): array
	{
		return $this->data ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Arbitrarily turnoff internationalization support for testing
	 */
	public function disableIntlSupport()
	{
		$this->intlSupport = false;
	}

}
