<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Cookie\CookieStore;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\Services;

/**
 * Handle a redirect response
 */
class RedirectResponse extends Response
{
    /**
     * Sets the URI to redirect to and, optionally, the HTTP status code to use.
     * If no code is provided it will be automatically determined.
     *
     * @param string   $uri  The URI path (relative to baseURL) to redirect to
     * @param int|null $code HTTP status code
     *
     * @return $this
     */
    public function to(string $uri, ?int $code = null, string $method = 'auto')
    {
        // If it appears to be a relative URL, then convert to full URL
        // for better security.
        if (strpos($uri, 'http') !== 0) {
            $uri = site_url($uri);
        }

        return $this->redirect($uri, $method, $code);
    }

    /**
     * Sets the URI to redirect to but as a reverse-routed or named route
     * instead of a raw URI.
     *
     * @param string $route Route name or Controller::method
     *
     * @return $this
     *
     * @throws HTTPException
     */
    public function route(string $route, array $params = [], ?int $code = null, string $method = 'auto')
    {
        $namedRoute = $route;

        $route = Services::routes()->reverseRoute($route, ...$params);

        if (! $route) {
            throw HTTPException::forInvalidRedirectRoute($namedRoute);
        }

        return $this->redirect(site_url($route), $method, $code);
    }

    /**
     * Helper function to return to previous page.
     *
     * Example:
     *  return redirect()->back();
     *
     * @return $this
     */
    public function back(?int $code = null, string $method = 'auto')
    {
        Services::session();

        return $this->redirect(previous_url(), $method, $code);
    }

    /**
     * Sets the current $_GET and $_POST arrays in the session.
     * This also saves the validation errors.
     *
     * It will then be available via the 'old()' helper function.
     *
     * @return $this
     */
    public function withInput()
    {
        $session = Services::session();
        $session->setFlashdata('_ci_old_input', [
            'get'  => $_GET ?? [],
            'post' => $_POST ?? [],
        ]);

        $this->withErrors();

        return $this;
    }

    /**
     * Sets validation errors in the session.
     *
     * If the validation has any errors, transmit those back
     * so they can be displayed when the validation is handled
     * within a method different than displaying the form.
     *
     * @return $this
     */
    private function withErrors(): self
    {
        $validation = Services::validation();

        if ($validation->getErrors()) {
            $session = Services::session();
            $session->setFlashdata('_ci_validation_errors', $validation->getErrors());
        }

        return $this;
    }

    /**
     * Adds a key and message to the session as Flashdata.
     *
     * @param array|string $message
     *
     * @return $this
     */
    public function with(string $key, $message)
    {
        Services::session()->setFlashdata($key, $message);

        return $this;
    }

    /**
     * Copies any cookies from the global Response instance
     * into this RedirectResponse. Useful when you've just
     * set a cookie but need ensure that's actually sent
     * with the response instead of lost.
     *
     * @return $this|RedirectResponse
     */
    public function withCookies()
    {
        $this->cookieStore = new CookieStore(Services::response()->getCookies());

        return $this;
    }

    /**
     * Copies any headers from the global Response instance
     * into this RedirectResponse. Useful when you've just
     * set a header be need to ensure its actually sent
     * with the redirect response.
     *
     * @return $this|RedirectResponse
     */
    public function withHeaders()
    {
        foreach (Services::response()->headers() as $name => $header) {
            $this->setHeader($name, $header->getValue());
        }

        return $this;
    }
}
