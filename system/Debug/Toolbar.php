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
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Debug;

use CodeIgniter\Debug\Toolbar\Collectors\History;
use CodeIgniter\Format\JSONFormatter;
use Config\Services;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Format\XMLFormatter;

/**
 * Debug Toolbar
 *
 * Displays a toolbar with bits of stats to aid a developer in debugging.
 *
 * Inspiration: http://prophiler.fabfuel.de
 *
 * @package CodeIgniter\Debug
 */
class Toolbar
{
	/**
	 * @var BaseConfig
	 */
	protected $config;

	/**
	 * Collectors to be used and displayed.
	 *
	 * @var \CodeIgniter\Debug\Toolbar\Collectors\BaseCollector[]
	 */
	protected $collectors = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config)
	{
		$this->config = $config;

		foreach ($config->collectors as $collector)
		{
			if (! class_exists($collector))
			{
				log_message('critical', 'Toolbar collector does not exists(' . $collector . ').' .
					'please check $collectors in the Config\Toolbar.php file.');
				continue;
			}

			$this->collectors[] = new $collector();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all the data required by Debug Bar
	 *
	 * @param float                               $startTime App start time
	 * @param float                               $totalTime
	 * @param \CodeIgniter\HTTP\RequestInterface  $request
	 * @param \CodeIgniter\HTTP\ResponseInterface $response
	 *
	 * @return string JSON encoded data
	 */
	public function run($startTime, $totalTime, $request, $response): string
	{
		// Data items used within the view.
		$data['url']             = current_url();
		$data['method']          = $request->getMethod(true);
		$data['isAJAX']          = $request->isAJAX();
		$data['startTime']       = $startTime;
		$data['totalTime']       = $totalTime * 1000;
		$data['totalMemory']     = number_format((memory_get_peak_usage()) / 1024 / 1024, 3);
		$data['segmentDuration'] = $this->roundTo($data['totalTime'] / 7, 5);
		$data['segmentCount']    = (int)ceil($data['totalTime'] / $data['segmentDuration']);
		$data['CI_VERSION']      = \CodeIgniter\CodeIgniter::CI_VERSION;
		$data['collectors']      = [];

		foreach ($this->collectors as $collector)
		{
			$data['collectors'][] = $collector->getAsArray();
		}

		foreach ($this->collectVarData() as $heading => $items)
		{
			$vardata = [];

			if (is_array($items))
			{
				foreach ($items as $key => $value)
				{
					$vardata[esc($key)] = is_string($value) ? esc($value) : print_r($value, true);
				}
			}

			$data['vars']['varData'][esc($heading)] = $vardata;
		}

		if (! empty($_SESSION))
		{
			foreach ($_SESSION as $key => $value)
			{
				// Replace the binary data with string to avoid json_encode failure.
				if (preg_match('~[^\x20-\x7E\t\r\n]~', $value))
				{
					$value = 'binary data';
				}

				$data['vars']['session'][esc($key)] = is_string($value) ? esc($value) : print_r($value, true);
			}
		}

		foreach ($request->getGet() as $name => $value)
		{
			$data['vars']['get'][esc($name)] = is_array($value) ? esc(print_r($value, true)) : esc($value);
		}

		foreach ($request->getPost() as $name => $value)
		{
			$data['vars']['post'][esc($name)] = is_array($value) ? esc(print_r($value, true)) : esc($value);
		}

		foreach ($request->getHeaders() as $header => $value)
		{
			if (empty($value))
			{
				continue;
			}

			if (! is_array($value))
			{
				$value = [$value];
			}

			foreach ($value as $h)
			{
				$data['vars']['headers'][esc($h->getName())] = esc($h->getValueLine());
			}
		}

		foreach ($request->getCookie() as $name => $value)
		{
			$data['vars']['cookies'][esc($name)] = esc($value);
		}

		$data['vars']['request'] = ($request->isSecure() ? 'HTTPS' : 'HTTP') . '/' . $request->getProtocolVersion();

		$data['vars']['response'] = [
			'statusCode'  => $response->getStatusCode(),
			'reason'      => esc($response->getReason()),
			'contentType' => esc($response->getHeaderLine('content-type')),
		];

		$data['config'] = \CodeIgniter\Debug\Toolbar\Collectors\Config::display();

		if ($response->CSP !== null)
		{
			$response->CSP->addImageSrc( 'data:' );
		}

		return json_encode($data);
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------

	/**
	 * Called within the view to display the timeline itself.
	 *
	 * @param array   $collectors
	 * @param float   $startTime
	 * @param integer $segmentCount
	 * @param integer $segmentDuration
	 *
	 * @return string
	 */
	protected function renderTimeline(array $collectors, $startTime, int $segmentCount, int $segmentDuration, array& $styles): string
	{
		$displayTime = $segmentCount * $segmentDuration;
		$rows        = $this->collectTimelineData($collectors);
		$output      = '';
		$styleCount  = 0;

		foreach ($rows as $row)
		{
			$output .= '<tr>';
			$output .= "<td>{$row['name']}</td>";
			$output .= "<td>{$row['component']}</td>";
			$output .= "<td class='debug-bar-alignRight'>" . number_format($row['duration'] * 1000, 2) . ' ms</td>';
			$output .= "<td class='debug-bar-noverflow' colspan='{$segmentCount}'>";

			$offset = ((($row['start'] - $startTime) * 1000) / $displayTime) * 100;
			$length = (($row['duration'] * 1000) / $displayTime) * 100;

			$styles['debug-bar-timeline-' . $styleCount] = "left: {$offset}%; width: {$length}%;";
			$output                                     .= "<span class='timer debug-bar-timeline-{$styleCount}' title='" . number_format($length,
					2) . "%'></span>";
			$output                                     .= '</td>';
			$output                                     .= '</tr>';

			$styleCount++;
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a sorted array of timeline data arrays from the collectors.
	 *
	 * @return array
	 */
	protected function collectTimelineData($collectors): array
	{
		$data = [];

		// Collect it
		foreach ($collectors as $collector)
		{
			if (! $collector['hasTimelineData'])
			{
				continue;
			}

			$data = array_merge($data, $collector['timelineData']);
		}

		// Sort it

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of data from all of the modules
	 * that should be displayed in the 'Vars' tab.
	 *
	 * @return array
	 */
	protected function collectVarData(): array
	{
		$data = [];

		foreach ($this->collectors as $collector)
		{
			if (! $collector->hasVarData())
			{
				continue;
			}

			$data = array_merge($data, $collector->getVarData());
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Rounds a number to the nearest incremental value.
	 *
	 * @param float   $number
	 * @param integer $increments
	 *
	 * @return float
	 */
	protected function roundTo($number, $increments = 5): float
	{
		$increments = 1 / $increments;

		return (ceil($number * $increments) / $increments);
	}

	//--------------------------------------------------------------------

	public function prepare()
	{
		if (CI_DEBUG && ! is_cli())
		{
			global $app;

			$request  = Services::request();
			$response = Services::response();

			$toolbar = Services::toolbar(config(Toolbar::class));
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

			if (! is_dir(WRITEPATH . 'debugbar'))
			{
				mkdir(WRITEPATH . 'debugbar', 0777);
			}

			write_file(WRITEPATH . 'debugbar/' . 'debugbar_' . $time . '.json', $data, 'w+');

			$format = $response->getHeaderLine('content-type');

			// Non-HTML formats should not include the debugbar
			// then we send headers saying where to find the debug data
			// for this response
			if ($request->isAJAX() || strpos($format, 'html') === false)
			{
				$response->setHeader('Debugbar-Time', $time)
						 ->setHeader('Debugbar-Link', site_url("?debugbar_time={$time}"))
						 ->getBody();

				return;
			}

			$script = PHP_EOL
				. '<script type="text/javascript" {csp-script-nonce} id="debugbar_loader" '
				. 'data-time="' . $time . '" '
				. 'src="' . site_url() . '?debugbar"></script>'
				. '<script type="text/javascript" {csp-script-nonce} id="debugbar_dynamic_script"></script>'
				. '<style type="text/css" {csp-style-nonce} id="debugbar_dynamic_style"></style>'
				. PHP_EOL;

			if (strpos($response->getBody(), '</body>') !== false)
			{
				$response->setBody(
					str_replace('</body>', $script . '</body>', $response->getBody())
				);

				return;
			}

			$response->appendBody($script);
		}
	}

	//--------------------------------------------------------------------

	/**
	 *
	 */
	public function respond()
	{
		if (ENVIRONMENT === 'testing')
		{
			return;
		}

		$request = Services::request();

		// If the request contains '?debugbar then we're
		// simply returning the loading script
		if ($request->getGet('debugbar') !== null)
		{
			// Let the browser know that we are sending javascript
			header('Content-Type: application/javascript');

			ob_start();
			include($this->config->viewsPath . 'toolbarloader.js.php');
			$output = ob_get_clean();

			exit($output);
		}

		// Otherwise, if it includes ?debugbar_time, then
		// we should return the entire debugbar.
		if ($request->getGet('debugbar_time'))
		{
			helper('security');

			// Negotiate the content-type to format the output
			$format = $request->negotiate('media', [
				'text/html',
				'application/json',
				'application/xml',
			]);
			$format = explode('/', $format)[1];

			$file     = sanitize_filename('debugbar_' . $request->getGet('debugbar_time'));
			$filename = WRITEPATH . 'debugbar/' . $file . '.json';

			// Show the toolbar
			if (is_file($filename))
			{
				$contents = $this->format(file_get_contents($filename), $format);
				exit($contents);
			}

			// File was not written or do not exists
			http_response_code(404);
			exit; // Exit here is needed to avoid load the index page
		}
	}

	/**
	 * Format output
	 *
	 * @param string $data   JSON encoded Toolbar data
	 * @param string $format html, json, xml
	 *
	 * @return string
	 */
	protected function format(string $data, string $format = 'html'): string
	{
		$data = json_decode($data, true);

		if ($this->config->maxHistory !== 0)
		{
			$history = new History();
			$history->setFiles(
				Services::request()->getGet('debugbar_time'),
				$this->config->maxHistory
			);

			$data['collectors'][] = $history->getAsArray();
		}

		$output = '';

		switch ($format)
		{
			case 'html':
				$data['styles'] = [];
				extract($data);
				$parser = Services::parser($this->config->viewsPath, null, false);
				ob_start();
				include($this->config->viewsPath . 'toolbar.tpl.php');
				$output = ob_get_clean();
				break;
			case 'json':
				$formatter = new JSONFormatter();
				$output    = $formatter->format($data);
				break;
			case 'xml':
				$formatter = new XMLFormatter;
				$output    = $formatter->format($data);
				break;
		}

		return $output;
	}
}
