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

namespace CodeIgniter\CLI;

/**
 * Text table with `+---+` borders and `|` separated cells.
 */
final readonly class Table
{
    /**
     * @param list<array<array-key, mixed>> $tbody List of rows
     * @param list<string>                  $thead List of columns
     */
    public function __construct(
        private array $tbody,
        private array $thead = [],
    ) {
    }

    /**
     * Returns the table as lines ending in `PHP_EOL`, or an empty string for no rows.
     */
    public function render(): string
    {
        $rows = $this->rows();

        if ($rows === []) {
            return '';
        }

        $widths = $this->columnWidths($rows);
        $border = $this->border($widths);
        $output = $border;
        $last   = array_key_last($rows);

        foreach ($rows as $index => $row) {
            $output .= $this->row($row, $widths);

            if (($index === 0 && $this->thead !== []) || $index === $last) {
                $output .= $border;
            }
        }

        return $output;
    }

    /**
     * @return list<list<string>>
     */
    private function rows(): array
    {
        $rows = $this->thead === [] ? [] : [$this->thead];

        foreach ($this->tbody as $row) {
            $rows[] = $row;
        }

        return array_map(
            static fn (array $row): array => array_map(static fn (mixed $cell): string => (string) $cell, array_values($row)),
            $rows,
        );
    }

    /**
     * @param list<list<string>> $rows
     *
     * @return list<int>
     */
    private function columnWidths(array $rows): array
    {
        $widths = [];

        foreach ($rows as $row) {
            foreach ($row as $column => $cell) {
                $widths[$column] = max($widths[$column] ?? 0, Color::width($cell));
            }
        }

        return $widths;
    }

    /**
     * @param list<int> $widths
     */
    private function border(array $widths): string
    {
        $segments = array_map(static fn (int $width): string => str_repeat('-', $width + 2) . '+', $widths);

        return '+' . implode('', $segments) . PHP_EOL;
    }

    /**
     * @param list<string> $row
     * @param list<int>    $widths
     */
    private function row(array $row, array $widths): string
    {
        $cells = [];

        foreach ($row as $column => $cell) {
            $cells[] = $cell . str_repeat(' ', $widths[$column] - Color::width($cell));
        }

        return sprintf('| %s |%s', implode(' | ', $cells), PHP_EOL);
    }
}
