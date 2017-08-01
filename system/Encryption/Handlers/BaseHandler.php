<?php namespace CodeIgniter\Encryption\Handlers;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use Psr\Log\LoggerAwareTrait;

/**
 * Base class for session handling
 */
abstract class BaseHandler implements \CodeIgniter\Encryption\EncrypterInterface
{

	use LoggerAwareTrait;

	/**
	 * Configuraiton passed from encryption manager
	 *
	 * @var	string
	 */
	protected $config;

	/**
	 * Derived secret key
	 *
	 * @var	string
	 */
	protected $secret;

	/**
	 * Derived HMAC digest key
	 *
	 * @var	string
	 */
	protected $hmacKey;

	/**
	 * Logger instance to record error messages and warnings.
	 * @var \PSR\Log\LoggerInterface
	 */
	protected $logger;

//--------------------------------------------------------------------

	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		$this->logger = \Config\Services::logger(true);

		if (empty($config))
			throw new EncryptionException("Encryption handler needs configuration parameters.");
		$this->config = $config;

		// make the parameters conveniently accessible
		foreach ($this->config as $pkey => $value)
			$this->$pkey = $value;
		
	}

// --------------------------------------------------------------------

	/**
	 * Byte-safe strlen()
	 *
	 * @param	string	$str
	 * @return	int
	 */
	protected static function strlen($str)
	{
		return mb_strlen($str, '8bit');
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe substr()
	 *
	 * @param	string	$str
	 * @param	int	$start
	 * @param	int	$length
	 * @return	string
	 */
	protected static function substr($str, $start, $length = null)
	{
		return mb_substr($str, $start, $length, '8bit');
	}

	/**
	 * __get() magic, providing readonly access to some of our properties
	 *
	 * @param	string	$key	Property name
	 * @return	mixed
	 */
	public function __get($key)
	{
		//FIXME
		if (in_array($key, ['cipher', 'key', 'hmac', 'digest', 'base64'], true))
		{
			return $this->{$key};
		}

		return null;
	}

}
