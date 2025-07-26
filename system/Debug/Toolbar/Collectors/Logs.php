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

namespace CodeIgniter\Debug\Toolbar\Collectors;

/**
 * Loags collector
 */
class Logs extends BaseCollector
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
    protected $title = 'Logs';

    /**
     * Our collected data.
     *
     * @var list<array{level: string, msg: string}>
     */
    protected $data;

    /**
     * Returns the data of this collector to be formatted in the toolbar.
     *
     * @return array{logs: list<array{level: string, msg: string}>}
     */
    public function display(): array
    {
        return [
            'logs' => $this->collectLogs(),
        ];
    }

    /**
     * Does this collector actually have any data to display?
     */
    public function isEmpty(): bool
    {
        $this->collectLogs();

        return $this->data !== [];
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACYSURBVEhLYxgFJIHU1FSjtLS0i0D8AYj7gEKMEBkqAaAFF4D4ERCvAFrwH4gDoFIMKSkpFkB+OTEYqgUTACXfA/GqjIwMQyD9H2hRHlQKJFcBEiMGQ7VgAqCBvUgK32dmZspCpagGGNPT0/1BLqeF4bQHQJePpiIwhmrBBEADR1MRfgB0+WgqAmOoFkwANHA0FY0CUgEDAwCQ0PUpNB3kqwAAAABJRU5ErkJggg==';
    }

    /**
     * Ensures the data has been collected.
     *
     * @return list<array{level: string, msg: string}>
     */
    protected function collectLogs()
    {
        if ($this->data !== []) {
            return $this->data;
        }

        $cache = service('logger')->logCache;

        $this->data = $cache ?? [];

        return $this->data;
    }
}
