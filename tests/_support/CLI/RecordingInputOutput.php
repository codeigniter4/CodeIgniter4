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

namespace Tests\Support\CLI;

use CodeIgniter\CLI\InputOutput;

/**
 * Transport that records each write with its stream and answers every prompt with a fixed value.
 */
final class RecordingInputOutput extends InputOutput
{
    /**
     * @var list<array{string, string}>
     */
    public array $writes = [];

    public ?string $receivedPrefix = null;

    public function __construct(private readonly string $answer = '')
    {
        parent::__construct();
    }

    public function input(?string $prefix = null): string
    {
        $this->receivedPrefix = $prefix;

        return $this->answer;
    }

    public function fwrite($handle, string $string): void
    {
        $this->writes[] = [$handle === STDERR ? 'STDERR' : 'STDOUT', $string];
    }
}
