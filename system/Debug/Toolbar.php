<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Debug\Toolbar\Collectors\BaseCollector;
use CodeIgniter\Debug\Toolbar\Collectors\Config;
use CodeIgniter\Debug\Toolbar\Collectors\History;
use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Services;
use Config\Toolbar as ToolbarConfig;
use Kint\Kint;

/**
 * Displays a toolbar with bits of stats to aid a developer in debugging.
 *
 * Inspiration: http://prophiler.fabfuel.de
 */
class Toolbar
{
    /**
     * Toolbar configuration settings.
     *
     * @var ToolbarConfig
     */
    protected $config;

    /**
     * Collectors to be used and displayed.
     *
     * @var BaseCollector[]
     */
    protected $collectors = [];

    public function __construct(ToolbarConfig $config)
    {
        $this->config = $config;

        foreach ($config->collectors as $collector) {
            if (! class_exists($collector)) {
                log_message(
                    'critical',
                    'Toolbar collector does not exist (' . $collector . ').'
                    . ' Please check $collectors in the app/Config/Toolbar.php file.'
                );

                continue;
            }

            $this->collectors[] = new $collector();
        }
    }

    /**
     * Returns all the data required by Debug Bar
     *
     * @param float           $startTime App start time
     * @param IncomingRequest $request
     *
     * @return string JSON encoded data
     */
    public function run(float $startTime, float $totalTime, RequestInterface $request, ResponseInterface $response): string
    {
        $data = [];
        // Data items used within the view.
        $data['url']             = current_url();
        $data['method']          = strtoupper($request->getMethod());
        $data['isAJAX']          = $request->isAJAX();
        $data['startTime']       = $startTime;
        $data['totalTime']       = $totalTime * 1000;
        $data['totalMemory']     = number_format((memory_get_peak_usage()) / 1024 / 1024, 3);
        $data['segmentDuration'] = $this->roundTo($data['totalTime'] / 7);
        $data['segmentCount']    = (int) ceil($data['totalTime'] / $data['segmentDuration']);
        $data['CI_VERSION']      = CodeIgniter::CI_VERSION;
        $data['collectors']      = [];

        foreach ($this->collectors as $collector) {
            $data['collectors'][] = $collector->getAsArray();
        }

        foreach ($this->collectVarData() as $heading => $items) {
            $varData = [];

            if (is_array($items)) {
                foreach ($items as $key => $value) {
                    if (is_string($value)) {
                        $varData[esc($key)] = esc($value);
                    } else {
                        $oldKintMode       = Kint::$mode_default;
                        $oldKintCalledFrom = Kint::$display_called_from;

                        Kint::$mode_default        = Kint::MODE_RICH;
                        Kint::$display_called_from = false;

                        $kint = @Kint::dump($value);
                        $kint = substr($kint, strpos($kint, '</style>') + 8);

                        Kint::$mode_default        = $oldKintMode;
                        Kint::$display_called_from = $oldKintCalledFrom;

                        $varData[esc($key)] = $kint;
                    }
                }
            }

            $data['vars']['varData'][esc($heading)] = $varData;
        }

        if (! empty($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                // Replace the binary data with string to avoid json_encode failure.
                if (is_string($value) && preg_match('~[^\x20-\x7E\t\r\n]~', $value)) {
                    $value = 'binary data';
                }

                $data['vars']['session'][esc($key)] = is_string($value) ? esc($value) : '<pre>' . esc(print_r($value, true)) . '</pre>';
            }
        }

        foreach ($request->getGet() as $name => $value) {
            $data['vars']['get'][esc($name)] = is_array($value) ? '<pre>' . esc(print_r($value, true)) . '</pre>' : esc($value);
        }

        foreach ($request->getPost() as $name => $value) {
            $data['vars']['post'][esc($name)] = is_array($value) ? '<pre>' . esc(print_r($value, true)) . '</pre>' : esc($value);
        }

        foreach ($request->headers() as $header) {
            $data['vars']['headers'][esc($header->getName())] = esc($header->getValueLine());
        }

        foreach ($request->getCookie() as $name => $value) {
            $data['vars']['cookies'][esc($name)] = esc($value);
        }

        $data['vars']['request'] = ($request->isSecure() ? 'HTTPS' : 'HTTP') . '/' . $request->getProtocolVersion();

        $data['vars']['response'] = [
            'statusCode'  => $response->getStatusCode(),
            'reason'      => esc($response->getReasonPhrase()),
            'contentType' => esc($response->getHeaderLine('content-type')),
            'headers'     => [],
        ];

        foreach ($response->headers() as $header) {
            $data['vars']['response']['headers'][esc($header->getName())] = esc($header->getValueLine());
        }

        $data['config'] = Config::display();

        $response->getCSP()->addImageSrc('data:');

        return json_encode($data);
    }

    /**
     * Called within the view to display the timeline itself.
     */
    protected function renderTimeline(array $collectors, float $startTime, int $segmentCount, int $segmentDuration, array &$styles): string
    {
        $rows       = $this->collectTimelineData($collectors);
        $styleCount = 0;

        // Use recursive render function
        return $this->renderTimelineRecursive($rows, $startTime, $segmentCount, $segmentDuration, $styles, $styleCount);
    }

    /**
     * Recursively renders timeline elements and their children.
     */
    protected function renderTimelineRecursive(array $rows, float $startTime, int $segmentCount, int $segmentDuration, array &$styles, int &$styleCount, int $level = 0, bool $isChild = false): string
    {
        $displayTime = $segmentCount * $segmentDuration;

        $output = '';

        foreach ($rows as $row) {
            $hasChildren = isset($row['children']) && ! empty($row['children']);
            $isQuery     = isset($row['query']) && ! empty($row['query']);

            // Open controller timeline by default
            $open = $row['name'] === 'Controller';

            if ($hasChildren || $isQuery) {
                $output .= '<tr class="timeline-parent' . ($open ? ' timeline-parent-open' : '') . '" id="timeline-' . $styleCount . '_parent" onclick="ciDebugBar.toggleChildRows(\'timeline-' . $styleCount . '\');">';
            } else {
                $output .= '<tr>';
            }

            $output .= '<td class="' . ($isChild ? 'debug-bar-width30' : '') . '" style="--level: ' . $level . ';">' . ($hasChildren || $isQuery ? '<nav></nav>' : '') . $row['name'] . '</td>';
            $output .= '<td class="' . ($isChild ? 'debug-bar-width10' : '') . '">' . $row['component'] . '</td>';
            $output .= '<td class="' . ($isChild ? 'debug-bar-width10 ' : '') . 'debug-bar-alignRight">' . number_format($row['duration'] * 1000, 2) . ' ms</td>';
            $output .= "<td class='debug-bar-noverflow' colspan='{$segmentCount}'>";

            $offset = ((((float) $row['start'] - $startTime) * 1000) / $displayTime) * 100;
            $length = (((float) $row['duration'] * 1000) / $displayTime) * 100;

            $styles['debug-bar-timeline-' . $styleCount] = "left: {$offset}%; width: {$length}%;";

            $output .= "<span class='timer debug-bar-timeline-{$styleCount}' title='" . number_format($length, 2) . "%'></span>";
            $output .= '</td>';
            $output .= '</tr>';

            $styleCount++;

            // Add children if any
            if ($hasChildren || $isQuery) {
                $output .= '<tr class="child-row" id="timeline-' . ($styleCount - 1) . '_children" style="' . ($open ? '' : 'display: none;') . '">';
                $output .= '<td colspan="' . ($segmentCount + 3) . '" class="child-container">';
                $output .= '<table class="timeline">';
                $output .= '<tbody>';

                if ($isQuery) {
                    // Output query string if query
                    $output .= '<tr>';
                    $output .= '<td class="query-container" style="--level: ' . ($level + 1) . ';">' . $row['query'] . '</td>';
                    $output .= '</tr>';
                } else {
                    // Recursively render children
                    $output .= $this->renderTimelineRecursive($row['children'], $startTime, $segmentCount, $segmentDuration, $styles, $styleCount, $level + 1, true);
                }

                $output .= '</tbody>';
                $output .= '</table>';
                $output .= '</td>';
                $output .= '</tr>';
            }
        }

        return $output;
    }

    /**
     * Returns a sorted array of timeline data arrays from the collectors.
     *
     * @param array $collectors
     */
    protected function collectTimelineData($collectors): array
    {
        $data = [];

        // Collect it
        foreach ($collectors as $collector) {
            if (! $collector['hasTimelineData']) {
                continue;
            }

            $data = array_merge($data, $collector['timelineData']);
        }

        // Sort it
        $sortArray = [
            array_column($data, 'start'), SORT_NUMERIC, SORT_ASC,
            array_column($data, 'duration'), SORT_NUMERIC, SORT_DESC,
            &$data,
        ];

        array_multisort(...$sortArray);

        // Add end time to each element
        array_walk($data, static function (&$row) {
            $row['end'] = $row['start'] + $row['duration'];
        });

        // Group it
        $data = $this->structureTimelineData($data);

        return $data;
    }

    /**
     * Arranges the already sorted timeline data into a parent => child structure.
     */
    protected function structureTimelineData(array $elements): array
    {
        // We define ourselves as the first element of the array
        $element = array_shift($elements);

        // If we have children behind us, collect and attach them to us
        while (! empty($elements) && $elements[array_key_first($elements)]['end'] <= $element['end']) {
            $element['children'][] = array_shift($elements);
        }

        // Make sure our children know whether they have children, too
        if (isset($element['children'])) {
            $element['children'] = $this->structureTimelineData($element['children']);
        }

        // If we have no younger siblings, we can return
        if (empty($elements)) {
            return [$element];
        }

        // Make sure our younger siblings know their relatives, too
        return array_merge([$element], $this->structureTimelineData($elements));
    }

    /**
     * Returns an array of data from all of the modules
     * that should be displayed in the 'Vars' tab.
     */
    protected function collectVarData(): array
    {
        if (! ($this->config->collectVarData ?? true)) {
            return [];
        }

        $data = [];

        foreach ($this->collectors as $collector) {
            if (! $collector->hasVarData()) {
                continue;
            }

            $data = array_merge($data, $collector->getVarData());
        }

        return $data;
    }

    /**
     * Rounds a number to the nearest incremental value.
     */
    protected function roundTo(float $number, int $increments = 5): float
    {
        $increments = 1 / $increments;

        return ceil($number * $increments) / $increments;
    }

    /**
     * Prepare for debugging..
     *
     * @return void
     */
    public function prepare(?RequestInterface $request = null, ?ResponseInterface $response = null)
    {
        /**
         * @var IncomingRequest|null $request
         */
        if (CI_DEBUG && ! is_cli()) {
            $app = Services::codeigniter();

            $request ??= Services::request();
            $response ??= Services::response();

            // Disable the toolbar for downloads
            if ($response instanceof DownloadResponse) {
                return;
            }

            $toolbar = Services::toolbar(config(ToolbarConfig::class));
            $stats   = $app->getPerformanceStats();
            $data    = $toolbar->run(
                $stats['startTime'],
                $stats['totalTime'],
                $request,
                $response
            );

            helper('filesystem');

            // Updated to microtime() so we can get history
            $time = sprintf('%.6f', Time::now()->format('U.u'));

            if (! is_dir(WRITEPATH . 'debugbar')) {
                mkdir(WRITEPATH . 'debugbar', 0777);
            }

            write_file(WRITEPATH . 'debugbar/debugbar_' . $time . '.json', $data, 'w+');

            $format = $response->getHeaderLine('content-type');

            // Non-HTML formats should not include the debugbar
            // then we send headers saying where to find the debug data
            // for this response
            if ($request->isAJAX() || strpos($format, 'html') === false) {
                $response->setHeader('Debugbar-Time', "{$time}")
                    ->setHeader('Debugbar-Link', site_url("?debugbar_time={$time}"));

                return;
            }

            $oldKintMode        = Kint::$mode_default;
            Kint::$mode_default = Kint::MODE_RICH;
            $kintScript         = @Kint::dump('');
            Kint::$mode_default = $oldKintMode;
            $kintScript         = substr($kintScript, 0, strpos($kintScript, '</style>') + 8);
            $kintScript         = ($kintScript === '0') ? '' : $kintScript;

            $script = PHP_EOL
                . '<script ' . csp_script_nonce() . ' id="debugbar_loader" '
                . 'data-time="' . $time . '" '
                . 'src="' . site_url() . '?debugbar"></script>'
                . '<script ' . csp_script_nonce() . ' id="debugbar_dynamic_script"></script>'
                . '<style ' . csp_style_nonce() . ' id="debugbar_dynamic_style"></style>'
                . $kintScript
                . PHP_EOL;

            if (strpos($response->getBody(), '<head>') !== false) {
                $response->setBody(
                    preg_replace(
                        '/<head>/',
                        '<head>' . $script,
                        $response->getBody(),
                        1
                    )
                );

                return;
            }

            $response->appendBody($script);
        }
    }

    /**
     * Inject debug toolbar into the response.
     *
     * @codeCoverageIgnore
     *
     * @return void
     * @phpstan-return never|void
     */
    public function respond()
    {
        if (ENVIRONMENT === 'testing') {
            return;
        }

        $request = Services::request();

        // If the request contains '?debugbar then we're
        // simply returning the loading script
        if ($request->getGet('debugbar') !== null) {
            header('Content-Type: application/javascript');

            ob_start();
            include $this->config->viewsPath . 'toolbarloader.js';
            $output = ob_get_clean();
            $output = str_replace('{url}', rtrim(site_url(), '/'), $output);
            echo $output;

            exit;
        }

        // Otherwise, if it includes ?debugbar_time, then
        // we should return the entire debugbar.
        if ($request->getGet('debugbar_time')) {
            helper('security');

            // Negotiate the content-type to format the output
            $format = $request->negotiate('media', ['text/html', 'application/json', 'application/xml']);
            $format = explode('/', $format)[1];

            $filename = sanitize_filename('debugbar_' . $request->getGet('debugbar_time'));
            $filename = WRITEPATH . 'debugbar/' . $filename . '.json';

            if (is_file($filename)) {
                // Show the toolbar if it exists
                echo $this->format(file_get_contents($filename), $format);

                exit;
            }

            // Filename not found
            http_response_code(404);

            exit; // Exit here is needed to avoid loading the index page
        }
    }

    /**
     * Format output
     */
    protected function format(string $data, string $format = 'html'): string
    {
        $data = json_decode($data, true);

        if ($this->config->maxHistory !== 0 && preg_match('/\d+\.\d{6}/s', (string) Services::request()->getGet('debugbar_time'), $debugbarTime)) {
            $history = new History();
            $history->setFiles(
                $debugbarTime[0],
                $this->config->maxHistory
            );

            $data['collectors'][] = $history->getAsArray();
        }

        $output = '';

        switch ($format) {
            case 'html':
                $data['styles'] = [];
                extract($data);
                $parser = Services::parser($this->config->viewsPath, null, false);
                ob_start();
                include $this->config->viewsPath . 'toolbar.tpl.php';
                $output = ob_get_clean();
                break;

            case 'json':
                $formatter = new JSONFormatter();
                $output    = $formatter->format($data);
                break;

            case 'xml':
                $formatter = new XMLFormatter();
                $output    = $formatter->format($data);
                break;
        }

        return $output;
    }
}
