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

namespace CodeIgniter\Honeypot;

use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Honeypot as HoneypotConfig;

/**
 * class Honeypot
 *
 * @see \CodeIgniter\Honeypot\HoneypotTest
 */
class Honeypot
{
    /**
     * Our configuration.
     *
     * @var HoneypotConfig
     */
    protected $config;

    /**
     * Constructor.
     *
     * @throws HoneypotException
     */
    public function __construct(HoneypotConfig $config)
    {
        $this->config = $config;

        if ($this->config->container === '' || ! str_contains($this->config->container, '{template}')) {
            $this->config->container = '<div style="display:none">{template}</div>';
        }

        $this->config->containerId ??= 'hpc';

        if ($this->config->template === '') {
            throw HoneypotException::forNoTemplate();
        }

        if ($this->config->name === '') {
            throw HoneypotException::forNoNameField();
        }
    }

    /**
     * Checks the request if honeypot field has data.
     *
     * @return bool
     */
    public function hasContent(RequestInterface $request)
    {
        assert($request instanceof IncomingRequest);

        return ! empty($request->getPost($this->config->name));
    }

    /**
     * Attaches Honeypot template to response.
     *
     * @return void
     */
    public function attachHoneypot(ResponseInterface $response)
    {
        if ($response->getBody() === null) {
            return;
        }

        if ($response->getCSP()->enabled()) {
            // Add id attribute to the container tag.
            $this->config->container = str_ireplace(
                '>{template}',
                ' id="' . $this->config->containerId . '">{template}',
                $this->config->container,
            );
        }

        $prepField = $this->prepareTemplate($this->config->template);

        $bodyBefore = $response->getBody();
        $bodyAfter  = str_ireplace('</form>', $prepField . '</form>', $bodyBefore);

        if ($response->getCSP()->enabled() && ($bodyBefore !== $bodyAfter)) {
            // Add style tag for the container tag in the head tag.
            $style     = '<style ' . csp_style_nonce() . '>#' . $this->config->containerId . ' { display:none }</style>';
            $bodyAfter = str_ireplace('</head>', $style . '</head>', $bodyAfter);
        }

        $response->setBody($bodyAfter);
    }

    /**
     * Prepares the template by adding label
     * content and field name.
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
