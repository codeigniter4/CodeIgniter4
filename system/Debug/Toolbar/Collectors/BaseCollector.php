<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

/**
 * Base Toolbar collector
 */
class BaseCollector
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
    protected $hasTabContent = false;

    /**
     * Whether this collector needs to display
     * a label or not.
     *
     * @var bool
     */
    protected $hasLabel = false;

    /**
     * Whether this collector has data that
     * should be shown in the Vars tab.
     *
     * @var bool
     */
    protected $hasVarData = false;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Gets the Collector's title.
     */
    public function getTitle(bool $safe = false): string
    {
        if ($safe) {
            return str_replace(' ', '-', strtolower($this->title));
        }

        return $this->title;
    }

    /**
     * Returns any information that should be shown next to the title.
     */
    public function getTitleDetails(): string
    {
        return '';
    }

    /**
     * Does this collector need it's own tab?
     */
    public function hasTabContent(): bool
    {
        return (bool) $this->hasTabContent;
    }

    /**
     * Does this collector have a label?
     */
    public function hasLabel(): bool
    {
        return (bool) $this->hasLabel;
    }

    /**
     * Does this collector have information for the timeline?
     */
    public function hasTimelineData(): bool
    {
        return (bool) $this->hasTimeline;
    }

    /**
     * Grabs the data for the timeline, properly formatted,
     * or returns an empty array.
     */
    public function timelineData(): array
    {
        if (! $this->hasTimeline) {
            return [];
        }

        return $this->formatTimelineData();
    }

    /**
     * Does this Collector have data that should be shown in the
     * 'Vars' tab?
     */
    public function hasVarData(): bool
    {
        return (bool) $this->hasVarData;
    }

    /**
     * Gets a collection of data that should be shown in the 'Vars' tab.
     * The format is an array of sections, each with their own array
     * of key/value pairs:
     *
     *  $data = [
     *      'section 1' => [
     *          'foo' => 'bar,
     *          'bar' => 'baz'
     *      ],
     *      'section 2' => [
     *          'foo' => 'bar,
     *          'bar' => 'baz'
     *      ],
     *  ];
     */
    public function getVarData()
    {
        return null;
    }

    /**
     * Child classes should implement this to return the timeline data
     * formatted for correct usage.
     *
     * Timeline data should be formatted into arrays that look like:
     *
     *  [
     *      'name'      => 'Database::Query',
     *      'component' => 'Database',
     *      'start'     => 10       // milliseconds
     *      'duration'  => 15       // milliseconds
     *  ]
     */
    protected function formatTimelineData(): array
    {
        return [];
    }

    /**
     * Returns the data of this collector to be formatted in the toolbar
     *
     * @return array|string
     */
    public function display()
    {
        return [];
    }

    /**
     * This makes nicer looking paths for the error output.
     *
     * @deprecated Use the dedicated `clean_path()` function.
     */
    public function cleanPath(string $file): string
    {
        return clean_path($file);
    }

    /**
     * Gets the "badge" value for the button.
     */
    public function getBadgeValue()
    {
        return null;
    }

    /**
     * Does this collector have any data collected?
     *
     * If not, then the toolbar button won't get shown.
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * Returns the HTML to display the icon. Should either
     * be SVG, or a base-64 encoded.
     *
     * Recommended dimensions are 24px x 24px
     */
    public function icon(): string
    {
        return '';
    }

    /**
     * Return settings as an array.
     */
    public function getAsArray(): array
    {
        return [
            'title'           => $this->getTitle(),
            'titleSafe'       => $this->getTitle(true),
            'titleDetails'    => $this->getTitleDetails(),
            'display'         => $this->display(),
            'badgeValue'      => $this->getBadgeValue(),
            'isEmpty'         => $this->isEmpty(),
            'hasTabContent'   => $this->hasTabContent(),
            'hasLabel'        => $this->hasLabel(),
            'icon'            => $this->icon(),
            'hasTimelineData' => $this->hasTimelineData(),
            'timelineData'    => $this->timelineData(),
        ];
    }
}
