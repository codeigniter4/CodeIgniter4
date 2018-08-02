<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;
use Config\Services;

class DebugToolbar implements FilterInterface
{
	/**
	 * We don't need to do anything here.
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * If the debug flag is set (CI_DEBUG) then collect performance
	 * and debug information and display it in a toolbar.
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response)
	{
		if ( ! is_cli() && CI_DEBUG)
		{
			global $app;

			$toolbar = Services::toolbar(config(App::class));
			$stats   = $app->getPerformanceStats();
			$data    = $toolbar->run(
				$stats['startTime'],
				$stats['totalTime'],
				$request,
				$response
			);

			helper('filesystem');

			// Updated to time() so we can get history
			$time = time();

			if (! is_dir(WRITEPATH.'debugbar'))
			{
				mkdir(WRITEPATH.'debugbar', 0777);
			}

			write_file(WRITEPATH .'debugbar/'.'debugbar_' . $time, $data, 'w+');

			$format = $response->getHeaderLine('content-type');

			// Non-HTML formats should not include the debugbar
			// then we send headers saying where to find the debug data
			// for this response
			if ($request->isAJAX() || strpos($format, 'html') === false)
			{
				return $response->setHeader('Debugbar-Time', (string)$time)
								->setHeader('Debugbar-Link', site_url("?debugbar_time={$time}"))
								->getBody();
			}

			$script = PHP_EOL
				. '<script type="text/javascript" {csp-script-nonce} id="debugbar_loader" '
				. 'data-time="' . $time . '" '
				. 'src="' . rtrim(site_url(), '/') . '?debugbar"></script>'
				. '<script type="text/javascript" {csp-script-nonce} id="debugbar_dynamic_script"></script>'
				. '<style type="text/css" {csp-style-nonce} id="debugbar_dynamic_style"></style>'
				. PHP_EOL;

			if (strpos($response->getBody(), '</body>') !== false)
			{
				return $response->setBody(str_replace('</body>', $script . '</body>',
					$response->getBody()));
			}

			return $response->appendBody($script);
		}
	}

	//--------------------------------------------------------------------
}
