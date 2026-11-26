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

namespace CodeIgniter\Debug\Inspection;

/**
 * A stack frame with its call and source location.
 *
 * @internal
 */
final readonly class Frame
{
    /**
     * Store a call with its source location and captured arguments.
     *
     * @param array<array-key, mixed> $arguments
     */
    public function __construct(
        public string $function,
        public ?string $file = null,
        public ?int $line = null,
        public ?string $class = null,
        public ?string $type = null,
        public array $arguments = [],
    ) {
    }

    /**
     * Render the call with preformatted arguments.
     */
    public function call(string $arguments = ''): string
    {
        $type = $this->type === '->' ? '()->' : ($this->type ?? '');

        return sprintf('%s%s%s(%s)', $this->class ?? '', $type, $this->function, $arguments);
    }

    /**
     * Export the available call and source metadata for API responses.
     *
     * @return array{file?: string, line?: int, class?: string, type?: string, function: string}
     */
    public function metadata(): array
    {
        return array_filter([
            'file'     => $this->file,
            'line'     => $this->line,
            'function' => $this->function,
            'class'    => $this->class,
            'type'     => $this->type,
        ], static fn (int|string|null $value): bool => $value !== null);
    }

    /**
     * Resolve nested eval locations to the outer eval call in the source file.
     *
     * @return array{file: string, line?: int}
     */
    public static function sourceLocation(string $file, ?int $line): array
    {
        if (preg_match('/^(.+?)\((\d+)\)(?: : eval\(\)\x27d code(?:\(\d+\))?)+$/D', $file, $parts) === 1) {
            return ['file' => $parts[1], 'line' => (int) $parts[2]];
        }

        return $line === null ? ['file' => $file] : ['file' => $file, 'line' => $line];
    }
}
