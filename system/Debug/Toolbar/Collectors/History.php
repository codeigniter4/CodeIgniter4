<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

/**
 * History collector
 */
class History extends BaseCollector
{
    /**
     * Whether this collector has data that can
     * be displayed in the Timeline.
     *
     * @var bool
     */
    protected $hasTimeline = false;

    /**
     * Whether this collector needs to display
     * content in a tab or not.
     *
     * @var bool
     */
    protected $hasTabContent = true;

    /**
     * Whether this collector needs to display
     * a label or not.
     *
     * @var bool
     */
    protected $hasLabel = true;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'History';

    /**
     * @var array History files
     */
    protected $files = [];

    //--------------------------------------------------------------------

    /**
     * Specify time limit & file count for debug history.
     *
     * @param int $current Current history time
     * @param int $limit   Max history files
     */
    public function setFiles(int $current, int $limit = 20)
    {
        $filenames = glob(WRITEPATH . 'debugbar/debugbar_*.json');

        $files   = [];
        $counter = 0;

        foreach (array_reverse($filenames) as $filename) {
            $counter++;

            // Oldest files will be deleted
            if ($limit >= 0 && $counter > $limit) {
                unlink($filename);

                continue;
            }

            // Get the contents of this specific history request
            $contents = file_get_contents($filename);

            $contents = @json_decode($contents);
            if (json_last_error() === JSON_ERROR_NONE) {
                preg_match_all('/\d+/', $filename, $time);
                $time = (int) end($time[0]);

                // Debugbar files shown in History Collector
                $files[] = [
                    'time'        => $time,
                    'datetime'    => date('Y-m-d H:i:s', $time),
                    'active'      => $time === $current,
                    'status'      => $contents->vars->response->statusCode,
                    'method'      => $contents->method,
                    'url'         => $contents->url,
                    'isAJAX'      => $contents->isAJAX ? 'Yes' : 'No',
                    'contentType' => $contents->vars->response->contentType,
                ];
            }
        }

        $this->files = $files;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the data of this collector to be formatted in the toolbar
     *
     * @return array
     */
    public function display(): array
    {
        return ['files' => $this->files];
    }

    //--------------------------------------------------------------------

    /**
     * Displays the number of included files as a badge in the tab button.
     *
     * @return int
     */
    public function getBadgeValue(): int
    {
        return count($this->files);
    }

    /**
     * Return true if there are no history files.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->files);
    }

    //--------------------------------------------------------------------

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     *
     * @return string
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAJySURBVEhL3ZU7aJNhGIVTpV6i4qCIgkIHxcXLErS4FBwUFNwiCKGhuTYJGaIgnRoo4qRu6iCiiIuIXXTTIkIpuqoFwaGgonUQlC5KafU5ycmNP0lTdPLA4fu+8573/a4/f6hXpFKpwUwmc9fDfweKbk+n07fgEv33TLSbtt/hvwNFT1PsG/zdTE0Gp+GFfD6/2fbVIxqNrqPIRbjg4t/hY8aztcngfDabHXbKyiiXy2vcrcPH8oDCry2FKDrA+Ar6L01E/ypyXzXaARjDGGcoeNxSDZXE0dHRA5VRE5LJ5CFy5jzJuOX2wHRHRnjbklZ6isQ3tIctBaAd4vlK3jLtkOVWqABBXd47jGHLmjTmSScttQV5J+SjfcUweFQEbsjAas5aqoCLXutJl7vtQsAzpRowYqkBinyCC8Vicb2lOih8zoldd0F8RD7qTFiqAnGrAy8stUAvi/hbqDM+YzkAFrLPdR5ZqoLXsd+Bh5YCIH7JniVdquUWxOPxDfboHhrI5XJ7HHhiqQXox+APe/Qk64+gGYVCYZs8cMpSFQj9JOoFzVqqo7k4HIvFYpscCoAjOmLffUsNUGRaQUwDlmofUa34ecsdgXdcXo4wbakBgiUFafXJV8A4DJ/2UrxUKm3E95H8RbjLcgOJRGILhnmCP+FBy5XvwN2uIPcy1AJvWgqC4xm2aU4Xb3lF4I+Tpyf8hRe5w3J7YLymSeA8Z3nSclv4WLRyFdfOjzrUFX0klJUEtZtntCNc+F69cz/FiDzEPtjzmcUMOr83kDQEX6pAJxJfpL3OX22n01YN7SZCoQnaSdoZ+Jz+PZihH3wt/xlCoT9M6nEtmRSPCQAAAABJRU5ErkJggg==';
    }
}
