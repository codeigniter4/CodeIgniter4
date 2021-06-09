<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Honeypot;

use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Honeypot as HoneypotConfig;

/**
 * class Honeypot
 */
class Honeypot
{
    /**
     * Our configuration.
     *
     * @var HoneypotConfig
     */
    protected $config;

    //--------------------------------------------------------------------

    /**
     * Constructor.
     *
     * @param HoneypotConfig $config
     *
     * @throws HoneypotException
     */
    public function __construct(HoneypotConfig $config)
    {
        $this->config = $config;

        if (! $this->config->hidden) {
            throw HoneypotException::forNoHiddenValue();
        }

        if (empty($this->config->container) || strpos($this->config->container, '{template}') === false) {
            $this->config->container = '<div style="display:none">{template}</div>';
        }

        if ($this->config->template === '') {
            throw HoneypotException::forNoTemplate();
        }

        if ($this->config->name === '') {
            throw HoneypotException::forNoNameField();
        }
    }

    //--------------------------------------------------------------------

    /**
     * Checks the request if honeypot field has data.
     *
     * @param RequestInterface $request
     */
    public function hasContent(RequestInterface $request)
    {
        return ! empty($request->getPost($this->config->name));
    }

    /**
     * Attaches Honeypot template to response.
     *
     * @param ResponseInterface $response
     */
    public function attachHoneypot(ResponseInterface $response)
    {
        $prepField = $this->prepareTemplate($this->config->template);

        $body = $response->getBody();
        $body = str_ireplace('</form>', $prepField . '</form>', $body);
        $response->setBody($body);
    }

    /**
     * Prepares the template by adding label
     * content and field name.
     *
     * @param string $template
     *
     * @return string
     */
    protected function prepareTemplate(string $template): string
    {
        $template = str_ireplace('{label}', $this->config->label, $template);
        $template = str_ireplace('{name}', $this->config->name, $template);

        if ($this->config->hidden) {
            $template = str_ireplace('{template}', $template, $this->config->container);
        }

        return $template;
    }
}
