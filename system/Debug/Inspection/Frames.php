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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Override;
use Throwable;
use Traversable;

/**
 * An ordered collection of normalized stack frames.
 *
 * @internal
 *
 * @implements IteratorAggregate<int, Frame>
 */
final readonly class Frames implements Countable, IteratorAggregate
{
    /**
     * Store normalized frames in trace order.
     *
     * @param list<Frame> $frames
     */
    public function __construct(private array $frames)
    {
    }

    /**
     * Normalize a trace using the exception's throw site.
     *
     * @param list<array{
     *   function: string,
     *   file?: string,
     *   line?: int,
     *   class?: string,
     *   type?: string,
     *   args?: array<array-key, mixed>
     * }>|null $trace
     */
    public static function fromException(Throwable $exception, ?array $trace = null): self
    {
        return self::fromTrace($trace ?? $exception->getTrace(), $exception->getFile(), $exception->getLine());
    }

    /**
     * Pair each raw call with the location inside that call.
     *
     * @param list<array{function: string, file?: string, line?: int, class?: string, type?: string, args?: array<array-key, mixed>}> $trace
     */
    public static function fromTrace(array $trace, ?string $file = null, ?int $line = null): self
    {
        if ($trace === [] && $file === null) {
            return new self([]);
        }

        $location = $file === null ? [] : Frame::sourceLocation($file, $line);
        $frames   = [];

        foreach ($trace as $row) {
            $function = $row['function'];

            if (str_starts_with($function, '{closure:') || str_ends_with($function, '\{closure}')) {
                $function = '{closure}';
            }

            $frames[] = new Frame(
                $function,
                $location['file'] ?? null,
                $location['line'] ?? null,
                $row['class'] ?? null,
                $row['type'] ?? null,
                $row['args'] ?? [],
            );

            $location = isset($row['file']) ? Frame::sourceLocation($row['file'], $row['line'] ?? null) : [];
        }

        // Use the final call site for the entry frame PHP omits.
        $frames[] = new Frame('{main}', $location['file'] ?? null, $location['line'] ?? null);

        return new self($frames);
    }

    /**
     * Count all frames before applying a display limit.
     */
    #[Override]
    public function count(): int
    {
        return count($this->frames);
    }

    /**
     * Iterate from the innermost call to the entry point.
     *
     * @return Traversable<int, Frame>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->frames);
    }

    /**
     * Return the leading frames up to the display limit.
     *
     * @return list<Frame>
     */
    public function take(int $limit): array
    {
        return array_slice($this->frames, 0, $limit);
    }
}
