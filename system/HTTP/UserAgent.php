<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use Config\UserAgents;
use Stringable;

/**
 * Abstraction for an HTTP user agent
 *
 * @see \CodeIgniter\HTTP\UserAgentTest
 */
class UserAgent implements Stringable
{
    /**
     * Current user-agent
     *
     * @var string
     */
    protected $agent = '';

    /**
     * Flag for if the user-agent belongs to a browser
     *
     * @var bool
     */
    protected $isBrowser = false;

    /**
     * Flag for if the user-agent is a robot
     *
     * @var bool
     */
    protected $isRobot = false;

    /**
     * Flag for if the user-agent is a mobile browser
     *
     * @var bool
     */
    protected $isMobile = false;

    /**
     * Holds the config file contents.
     *
     * @var UserAgents
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
     * @var bool|string|null
     */
    protected $referrer;

    /**
     * Constructor
     *
     * Sets the User Agent and runs the compilation routine
     */
    public function __construct(?UserAgents $config = null)
    {
        $this->config = $config ?? config(UserAgents::class);

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->agent = trim($_SERVER['HTTP_USER_AGENT']);
            $this->compileData();
        }
    }

    /**
     * Is Browser
     */
    public function isBrowser(?string $key = null): bool
    {
        if (! $this->isBrowser) {
            return false;
        }

        // No need to be specific, it's a browser
        if ((string) $key === '') {
            return true;
        }

        // Check for a specific browser
        return isset($this->config->browsers[$key]) && $this->browser === $this->config->browsers[$key];
    }

    /**
     * Is Robot
     */
    public function isRobot(?string $key = null): bool
    {
        if (! $this->isRobot) {
            return false;
        }

        // No need to be specific, it's a robot
        if ((string) $key === '') {
            return true;
        }

        // Check for a specific robot
        return isset($this->config->robots[$key]) && $this->robot === $this->config->robots[$key];
    }

    /**
     * Is Mobile
     */
    public function isMobile(?string $key = null): bool
    {
        if (! $this->isMobile) {
            return false;
        }

        // No need to be specific, it's a mobile
        if ((string) $key === '') {
            return true;
        }

        // Check for a specific robot
        return isset($this->config->mobiles[$key]) && $this->mobile === $this->config->mobiles[$key];
    }

    /**
     * Is this a referral from another site?
     */
    public function isReferral(): bool
    {
        if (! isset($this->referrer)) {
            if (empty($_SERVER['HTTP_REFERER'])) {
                $this->referrer = false;
            } else {
                $refererHost = @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
                $ownHost     = parse_url(\base_url(), PHP_URL_HOST);

                $this->referrer = ($refererHost && $refererHost !== $ownHost);
            }
        }

        return $this->referrer;
    }

    /**
     * Agent String
     */
    public function getAgentString(): string
    {
        return $this->agent;
    }

    /**
     * Get Platform
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * Get Browser Name
     */
    public function getBrowser(): string
    {
        return $this->browser;
    }

    /**
     * Get the Browser Version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get The Robot Name
     */
    public function getRobot(): string
    {
        return $this->robot;
    }

    /**
     * Get the Mobile Device
     */
    public function getMobile(): string
    {
        return $this->mobile;
    }

    /**
     * Get the referrer
     */
    public function getReferrer(): string
    {
        return empty($_SERVER['HTTP_REFERER']) ? '' : trim($_SERVER['HTTP_REFERER']);
    }

    /**
     * Parse a custom user-agent string
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

        if ($string !== '') {
            $this->compileData();
        }
    }

    /**
     * Compile the User Agent Data
     *
     * @return void
     */
    protected function compileData()
    {
        $this->setPlatform();

        foreach (['setRobot', 'setBrowser', 'setMobile'] as $function) {
            if ($this->{$function}()) {
                break;
            }
        }
    }

    /**
     * Set the Platform
     */
    protected function setPlatform(): bool
    {
        if (is_array($this->config->platforms) && $this->config->platforms !== []) {
            foreach ($this->config->platforms as $key => $val) {
                if (preg_match('|' . preg_quote($key, '|') . '|i', $this->agent)) {
                    $this->platform = $val;

                    return true;
                }
            }
        }

        $this->platform = 'Unknown Platform';

        return false;
    }

    /**
     * Set the Browser
     */
    protected function setBrowser(): bool
    {
        if (is_array($this->config->browsers) && $this->config->browsers !== []) {
            foreach ($this->config->browsers as $key => $val) {
                if (preg_match('|' . $key . '.*?([0-9\.]+)|i', $this->agent, $match)) {
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

    /**
     * Set the Robot
     */
    protected function setRobot(): bool
    {
        if (is_array($this->config->robots) && $this->config->robots !== []) {
            foreach ($this->config->robots as $key => $val) {
                if (preg_match('|' . preg_quote($key, '|') . '|i', $this->agent)) {
                    $this->isRobot = true;
                    $this->robot   = $val;
                    $this->setMobile();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set the Mobile Device
     */
    protected function setMobile(): bool
    {
        if (is_array($this->config->mobiles) && $this->config->mobiles !== []) {
            foreach ($this->config->mobiles as $key => $val) {
                if (false !== (stripos($this->agent, $key))) {
                    $this->isMobile = true;
                    $this->mobile   = $val;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Outputs the original Agent String when cast as a string.
     */
    public function __toString(): string
    {
        return $this->getAgentString();
    }
}
