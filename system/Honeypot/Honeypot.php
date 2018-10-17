<?php

namespace CodeIgniter\Honeypot;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;

class Honeypot
{

	/**
	 * @var BaseConfig
	 */
	protected $config;

	//--------------------------------------------------------------------

	function __construct(BaseConfig $config)
	{
		$this->config = $config;

		if ($this->config->hidden === '')
		{
			throw HoneypotException::forNoHiddenValue();
		}

		if ($this->config->template === '')
		{
			throw HoneypotException::forNoTemplate();
		}

		if ($this->config->name === '')
		{
			throw HoneypotException::forNoNameField();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the request if honeypot field has data.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 * 
	 */
	public function hasContent(RequestInterface $request)
	{
		return ( ! empty($request->getPost($this->config->name))) ? true : false;
	}

	/**
	 * Attaches Honeypot template to response.
	 *
	 * @param \CodeIgniter\HTTP\ResponseInterface $response
	 */
	public function attachHoneypot(ResponseInterface $response)
	{
		$prep_field = $this->prepareTemplate($this->config->template);

		$body = $response->getBody();
		$body = str_ireplace('</form>', $prep_field, $body);
		$response->setBody($body);
	}

	/**
	 * Prepares the template by adding label
	 * content and field name.
	 *
	 * @param string $template
	 * @return string
	 */
	protected function prepareTemplate($template): string
	{
		$template = str_ireplace('{label}', $this->config->label, $template);
		$template = str_ireplace('{name}', $this->config->name, $template);

		if ($this->config->hidden)
		{
			$template = '<div style="display:none">' . $template . '</div>';
		}
		return $template;
	}

}
