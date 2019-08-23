<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP;

use Config\UserAgents;

/**
 * Abstraction for an HTTP user agent
 */
class UserAgent
{
	/**
	 * Current user-agent
	 *
	 * @var string
	 */
	protected $agent = null;

	/**
	 * Flag for if the user-agent belongs to a browser
	 *
	 * @var boolean
	 */
	protected $isBrowser = false;

	/**
	 * Flag for if the user-agent is a robot
	 *
	 * @var boolean
	 */
	protected $isRobot = false;

	/**
	 * Flag for if the user-agent is a mobile browser
	 *
	 * @var boolean
	 */
	protected $isMobile = false;

	/**
	 * Holds the config file contents.
	 *
	 * @var \Config\UserAgents
	 */
	protected $config;

	/**
	 * Current user-agent platform
	 *
	 * @var string
	 */
	protected $platform = '';

	/**
	 * Current user-agent browser
	 *
	 * @var string
	 */
	protected $browser = '';

	/**
	 * Current user-agent version
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * Current user-agent mobile name
	 *
	 * @var string
	 */
	protected $mobile = '';

	/**
	 * Current user-agent robot name
	 *
	 * @var string
	 */
	protected $robot = '';

	/**
	 * HTTP Referer
	 *
	 * @var mixed
	 */
	protected $referrer;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * Sets the User Agent and runs the compilation routine
	 *
	 * @param null $config
	 */
	public function __construct($config = null)
	{
		if (is_null($config))
		{
			$this->config = new UserAgents();
		}

		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
			$this->compileData();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Is Browser
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function isBrowser(string $key = null): bool
	{
		if (! $this->isBrowser)
		{
			return false;
		}

		// No need to be specific, it's a browser
		if ($key === null)
		{
			return true;
		}

		// Check for a specific browser
		return (isset($this->config->browsers[$key]) && $this->browser === $this->config->browsers[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is Robot
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function isRobot(string $key = null): bool
	{
		if (! $this->isRobot)
		{
			return false;
		}

		// No need to be specific, it's a robot
		if ($key === null)
		{
			return true;
		}

		// Check for a specific robot
		return (isset($this->config->robots[$key]) && $this->robot === $this->config->robots[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is Mobile
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function isMobile(string $key = null): bool
	{
		if (! $this->isMobile)
		{
			return false;
		}

		// No need to be specific, it's a mobile
		if ($key === null)
		{
			return true;
		}

		// Check for a specific robot
		return (isset($this->config->mobiles[$key]) && $this->mobile === $this->config->mobiles[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is this a referral from another site?
	 *
	 * @return boolean
	 */
	public function isReferral(): bool
	{
		if (! isset($this->referrer))
		{
			if (empty($_SERVER['HTTP_REFERER']))
			{
				$this->referrer = false;
			}
			else
			{
				$referer_host = @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
				$own_host     = parse_url(\base_url(), PHP_URL_HOST);

				$this->referrer = ($referer_host && $referer_host !== $own_host);
			}
		}

		return $this->referrer;
	}

	//--------------------------------------------------------------------

	/**
	 * Agent String
	 *
	 * @return string
	 */
	public function getAgentString(): string
	{
		return $this->agent;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Platform
	 *
	 * @return string
	 */
	public function getPlatform(): string
	{
		return $this->platform;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Browser Name
	 *
	 * @return string
	 */
	public function getBrowser(): string
	{
		return $this->browser;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the Browser Version
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	//--------------------------------------------------------------------

	/**
	 * Get The Robot Name
	 *
	 * @return string
	 */
	public function getRobot(): string
	{
		return $this->robot;
	}
	//--------------------------------------------------------------------

	/**
	 * Get the Mobile Device
	 *
	 * @return string
	 */
	public function getMobile(): string
	{
		return $this->mobile;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the referrer
	 *
	 * @return string
	 */
	public function getReferrer(): string
	{
		return empty($_SERVER['HTTP_REFERER']) ? '' : trim($_SERVER['HTTP_REFERER']);
	}

	//--------------------------------------------------------------------

	/**
	 * Parse a custom user-agent string
	 *
	 * @param string $string
	 *
	 * @return void
	 */
	public function parse(string $string)
	{
		// Reset values
		$this->isBrowser = false;
		$this->isRobot   = false;
		$this->isMobile  = false;
		$this->browser   = '';
		$this->version   = '';
		$this->mobile    = '';
		$this->robot     = '';

		// Set the new user-agent string and parse it, unless empty
		$this->agent = $string;

		if (! empty($string))
		{
			$this->compileData();
		}
	}
	//--------------------------------------------------------------------

	/**
	 * Compile the User Agent Data
	 *
	 * @return void
	 */
	protected function compileData()
	{
		$this->setPlatform();

		foreach (['setRobot', 'setBrowser', 'setMobile'] as $function)
		{
			if ($this->$function() === true)
			{
				break;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Platform
	 *
	 * @return boolean
	 */
	protected function setPlatform(): bool
	{
		if (is_array($this->config->platforms) && $this->config->platforms)
		{
			foreach ($this->config->platforms as $key => $val)
			{
				if (preg_match('|' . preg_quote($key) . '|i', $this->agent))
				{
					$this->platform = $val;

					return true;
				}
			}
		}

		$this->platform = 'Unknown Platform';

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Browser
	 *
	 * @return boolean
	 */
	protected function setBrowser(): bool
	{
		if (is_array($this->config->browsers) && $this->config->browsers)
		{
			foreach ($this->config->browsers as $key => $val)
			{
				if (preg_match('|' . $key . '.*?([0-9\.]+)|i', $this->agent, $match))
				{
					$this->isBrowser = true;
					$this->version   = $match[1];
					$this->browser   = $val;
					$this->setMobile();

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Robot
	 *
	 * @return boolean
	 */
	protected function setRobot(): bool
	{
		if (is_array($this->config->robots) && $this->config->robots)
		{
			foreach ($this->config->robots as $key => $val)
			{
				if (preg_match('|' . preg_quote($key) . '|i', $this->agent))
				{
					$this->isRobot = true;
					$this->robot   = $val;
					$this->setMobile();

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 *
	 * @return boolean
	 */
	protected function setMobile(): bool
	{
		if (is_array($this->config->mobiles) && $this->config->mobiles)
		{
			foreach ($this->config->mobiles as $key => $val)
			{
				if (false !== (stripos($this->agent, $key)))
				{
					$this->isMobile = true;
					$this->mobile   = $val;

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs the original Agent String when cast as a string.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getAgentString();
	}

}
