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
 * Files collector
 */
class Files extends BaseCollector
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
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Files';

    //--------------------------------------------------------------------

    /**
     * Returns any information that should be shown next to the title.
     *
     * @return string
     */
    public function getTitleDetails(): string
    {
        return '( ' . (int) count(get_included_files()) . ' )';
    }

    //--------------------------------------------------------------------

    /**
     * Returns the data of this collector to be formatted in the toolbar
     *
     * @return array
     */
    public function display(): array
    {
        $rawFiles  = get_included_files();
        $coreFiles = [];
        $userFiles = [];

        foreach ($rawFiles as $file) {
            $path = $this->cleanPath($file);

            if (strpos($path, 'SYSTEMPATH') !== false) {
                $coreFiles[] = [
                    'name' => basename($file),
                    'path' => $path,
                ];
            } else {
                $userFiles[] = [
                    'name' => basename($file),
                    'path' => $path,
                ];
            }
        }

        sort($userFiles);
        sort($coreFiles);

        return [
            'coreFiles' => $coreFiles,
            'userFiles' => $userFiles,
        ];
    }

    //--------------------------------------------------------------------

    /**
     * Displays the number of included files as a badge in the tab button.
     *
     * @return int
     */
    public function getBadgeValue(): int
    {
        return count(get_included_files());
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
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAGBSURBVEhL7ZQ9S8NQGIVTBQUncfMfCO4uLgoKbuKQOWg+OkXERRE1IAXrIHbVDrqIDuLiJgj+gro7S3dnpfq88b1FMTE3VZx64HBzzvvZWxKnj15QCcPwCD5HUfSWR+JtzgmtsUcQBEva5IIm9SwSu+95CAWbUuy67qBa32ByZEDpIaZYZSZMjjQuPcQUq8yEyYEb8FSerYeQVGbAFzJkX1PyQWLhgCz0BxTCekC1Wp0hsa6yokzhed4oje6Iz6rlJEkyIKfUEFtITVtQdAibn5rMyaYsMS+a5wTv8qeXMhcU16QZbKgl3hbs+L4/pnpdc87MElZgq10p5DxGdq8I7xrvUWUKvG3NbSK7ubngYzdJwSsF7TiOh9VOgfcEz1UayNe3JUPM1RWC5GXYgTfc75B4NBmXJnAtTfpABX0iPvEd9ezALwkplCFXcr9styiNOKc1RRZpaPM9tcqBwlWzGY1qPL9wjqRBgF5BH6j8HWh2S7MHlX8PrmbK+k/8PzjOOzx1D3i1pKTTAAAAAElFTkSuQmCC';
    }
}
