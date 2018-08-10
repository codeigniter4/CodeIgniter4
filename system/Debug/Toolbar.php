<?php namespace CodeIgniter\Debug;

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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */
use Config\App;
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
	 * Collectors to be used and displayed.
	 *
	 * @var array
	 */
	protected $collectors = [];

	/**
	 * Incoming Request
	 *
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected static $request;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config)
	{
		foreach ($config->toolbarCollectors as $collector)
		{
			if (! class_exists($collector))
			{
				// @todo Log this!
				continue;
			}

			$this->collectors[] = new $collector();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all the data required by Debug Bar
	 *
	 * @param float                               $startTime   App start time
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
		$data['totalTime']       = $totalTime*1000;
		$data['totalMemory']     = number_format((memory_get_peak_usage())/1024/1024, 3);
		$data['segmentDuration'] = $this->roundTo($data['totalTime']/7, 5);
		$data['segmentCount']    = (int)ceil($data['totalTime']/$data['segmentDuration']);
		$data['CI_VERSION']      = \CodeIgniter\CodeIgniter::CI_VERSION;
		$data['collectors']      = [];

		foreach($this->collectors as $collector)
		{
			$data['collectors'][] = [
				'title'           => $collector->getTitle(),
				'titleSafe'       => $collector->getTitle(true),
				'titleDetails'    => $collector->getTitleDetails(),
				'display'         => $collector->display(),
				'badgeValue'      => $collector->getBadgeValue(),
				'isEmpty'         => $collector->isEmpty(),
				'hasTabContent'   => $collector->hasTabContent(),
				'hasLabel'        => $collector->hasLabel(),
				'icon'            => $collector->icon(),
				'hasTimelineData' => $collector->hasTimelineData(),
				'timelineData'    => $collector->timelineData(),
			];
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

		$data['vars']['request'] = ($request->isSecure() ? 'HTTPS' : 'HTTP').'/'.$request->getProtocolVersion();

		$data['vars']['response'] = [
			'statusCode'      => $response->getStatusCode(),
			'reason'          => esc($response->getReason()),
			'contentType'     => esc($response->getHeaderLine('content-type')),
		];

		$data['config'] = \CodeIgniter\Debug\Toolbar\Collectors\Config::display();

		if( $response->CSP !== null )
		{
			$response->CSP->addImageSrc( 'data:' );
		}

		return json_encode($data);
	}

	//--------------------------------------------------------------------

	/**
	 * Format output
	 *
	 * @param  string $data   JSON encoded Toolbar data
	 * @param  string $format html, json, xml
	 *
	 * @return string
	 */
	protected static function format(string $data, string $format = 'html')
	{
		$data = json_decode($data, true);

		// History must be loaded on the fly
		$filenames = glob(WRITEPATH.'debugbar/debugbar_*');
		$total     = count($filenames);
		rsort($filenames);

		$files = [];

		$current = self::$request->getGet('debugbar_time');
		$app     = config(App::class);

		for ($i = 0; $i < $total; $i++)
		{
                        // Oldest files will be deleted
			if ($app->toolbarMaxHistory >= 0 && $i+1 > $app->toolbarMaxHistory)
			{
				unlink($filenames[$i]);
				continue;
                        }

			// Get the contents of this specific history request
			ob_start();
			include($filenames[$i]);
			$contents = ob_get_contents();
			ob_end_clean();

			$file = json_decode($contents, true);

			// Debugbar files shown in History Collector
			$files[] = [
				'time'        => (int)$time = substr($filenames[$i], -10),
				'datetime'    => date('Y-m-d H:i:s', $time),
				'active'      => (int)($time == $current),
				'status'      => $file['vars']['response']['statusCode'],
				'method'      => $file['method'],
				'url'         => $file['url'],
				'isAJAX'      => $file['isAJAX'] ? 'Yes' : 'No',
				'contentType' => $file['vars']['response']['contentType'],
			];
		}

		// Set the History here. Class is not necessary
		$data['collectors'][] = [
			'title'           => 'History',
			'titleSafe'       => 'history',
			'titleDetails'    => '',
			'display'         => ['files' => $files],
			'badgeValue'      => $count = count($files),
			'isEmpty'         => ! (bool)$count,
			'hasTabContent'   => true,
			'hasLabel'        => true,
			'icon'            => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAJySURBVEhL3ZU7aJNhGIVTpV6i4qCIgkIHxcXLErS4FBwUFNwiCKGhuTYJGaIgnRoo4qRu6iCiiIuIXXTTIkIpuqoFwaGgonUQlC5KafU5ycmNP0lTdPLA4fu+8573/a4/f6hXpFKpwUwmc9fDfweKbk+n07fgEv33TLSbtt/hvwNFT1PsG/zdTE0Gp+GFfD6/2fbVIxqNrqPIRbjg4t/hY8aztcngfDabHXbKyiiXy2vcrcPH8oDCry2FKDrA+Ar6L01E/ypyXzXaARjDGGcoeNxSDZXE0dHRA5VRE5LJ5CFy5jzJuOX2wHRHRnjbklZ6isQ3tIctBaAd4vlK3jLtkOVWqABBXd47jGHLmjTmSScttQV5J+SjfcUweFQEbsjAas5aqoCLXutJl7vtQsAzpRowYqkBinyCC8Vicb2lOih8zoldd0F8RD7qTFiqAnGrAy8stUAvi/hbqDM+YzkAFrLPdR5ZqoLXsd+Bh5YCIH7JniVdquUWxOPxDfboHhrI5XJ7HHhiqQXox+APe/Qk64+gGYVCYZs8cMpSFQj9JOoFzVqqo7k4HIvFYpscCoAjOmLffUsNUGRaQUwDlmofUa34ecsdgXdcXo4wbakBgiUFafXJV8A4DJ/2UrxUKm3E95H8RbjLcgOJRGILhnmCP+FBy5XvwN2uIPcy1AJvWgqC4xm2aU4Xb3lF4I+Tpyf8hRe5w3J7YLymSeA8Z3nSclv4WLRyFdfOjzrUFX0klJUEtZtntCNc+F69cz/FiDzEPtjzmcUMOr83kDQEX6pAJxJfpL3OX22n01YN7SZCoQnaSdoZ+Jz+PZihH3wt/xlCoT9M6nEtmRSPCQAAAABJRU5ErkJggg==',
			'hasTimelineData' => false,
			'timelineData'    => [],
		];

		$output = '';

		switch ($format)
		{
			case 'html':
				$data['styles'] = [];
				extract($data);
				$parser = Services::parser(BASEPATH . 'Debug/Toolbar/Views/', null,false);
				ob_start();
				include(__DIR__.'/Toolbar/Views/toolbar.tpl.php');
				$output = ob_get_contents();
				ob_end_clean();
				break;
			case 'json':
				$output = json_encode($data);
				break;
			case 'xml':
				$formatter = new XMLFormatter;
				$output    = $formatter->format($data);
				break;
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Called within the view to display the timeline itself.
	 *
	 * @param array $collectors
	 * @param float $startTime
	 * @param int   $segmentCount
	 * @param int   $segmentDuration
	 *
	 * @return string
	 */
	protected static function renderTimeline(array $collectors, $startTime, int $segmentCount, int $segmentDuration, array& $styles ): string
	{
		$displayTime = $segmentCount*$segmentDuration;
		$rows        = self::collectTimelineData($collectors);
		$output      = '';
		$styleCount	 = 0;

		foreach ($rows as $row)
		{
			$output .= "<tr>";
			$output .= "<td>{$row['name']}</td>";
			$output .= "<td>{$row['component']}</td>";
			$output .= "<td class='debug-bar-alignRight'>".number_format($row['duration']*1000, 2)." ms</td>";
			$output .= "<td class='debug-bar-noverflow' colspan='{$segmentCount}'>";

			$offset = ((($row['start']-$startTime)*1000)/$displayTime)*100;
			$length = (($row['duration']*1000)/$displayTime)*100;

			$styles['debug-bar-timeline-'.$styleCount] = "left: {$offset}%; width: {$length}%;";
			$output .= "<span class='timer debug-bar-timeline-{$styleCount}' title='".number_format($length,
					2)."%'></span>";
			$output .= "</td>";
			$output .= "</tr>";

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
	protected static function collectTimelineData($collectors): array
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
	protected function collectVarData()// : array
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
	 * @param float $number
	 * @param int   $increments
	 *
	 * @return float
	 */
	protected function roundTo($number, $increments = 5)
	{
		$increments = 1/$increments;

		return (ceil($number*$increments)/$increments);
	}

	//--------------------------------------------------------------------

	/**
	 *
	 */
	public static function eventHandler()
	{
		self::$request = Services::request();

		if(ENVIRONMENT == 'testing')
		{
			return;
		}

		// If the request contains '?debugbar then we're
		// simply returning the loading script
		if (self::$request->getGet('debugbar') !== null)
		{
			// Let the browser know that we are sending javascript
			header('Content-Type: application/javascript');

			ob_start();
			include(BASEPATH.'Debug/Toolbar/toolbarloader.js.php');
			$output = ob_get_contents();
			@ob_end_clean();

			exit($output);
		}

		// Otherwise, if it includes ?debugbar_time, then
		// we should return the entire debugbar.
		if (self::$request->getGet('debugbar_time'))
		{
			helper('security');

			// Negotiate the content-type to format the output
			$format = self::$request->negotiate('media', [
				'text/html',
				'application/json',
				'application/xml'
			]);
			$format = explode('/', $format)[1];

			$file     = sanitize_filename('debugbar_'.self::$request->getGet('debugbar_time'));
			$filename = WRITEPATH.'debugbar/'.$file;

			// Show the toolbar
			if (file_exists($filename))
			{
				$contents = self::format(file_get_contents($filename), $format);
				exit($contents);
			}

			// File was not written or do not exists
			http_response_code(404);
			exit(); // Exit here is needed to avoid load the index page
		}
	}
}
