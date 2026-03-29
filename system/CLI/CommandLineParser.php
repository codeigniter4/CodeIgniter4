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

final class CommandLineParser
{
    /**
     * @var list<string>
     */
    private array $arguments = [];

    /**
     * @var array<string, string|null>
     */
    private array $options = [];

    /**
     * @var array<int|string, string|null>
     */
    private array $tokens = [];

    /**
     * @param list<string> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->parseTokens($tokens);
    }

    /**
     * @return list<string>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<string, string|null>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array<int|string, string|null>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @param list<string> $tokens
     */
    private function parseTokens(array $tokens): void
    {
        array_shift($tokens); // Remove the application name

        $parseOptions = true;
        $optionValue  = false;

        foreach ($tokens as $index => $token) {
            if ($token === '--' && $parseOptions) {
                $parseOptions = false;

                continue;
            }

            if (str_starts_with($token, '-') && $parseOptions) {
                $name  = ltrim($token, '-');
                $value = null;

                if (str_contains($name, '=')) {
                    [$name, $value] = explode('=', $name, 2);
                } elseif (isset($tokens[$index + 1]) && ! str_starts_with($tokens[$index + 1], '-')) {
                    $value = $tokens[$index + 1];

                    $optionValue = true;
                }

                $this->tokens[$name]  = $value;
                $this->options[$name] = $value;

                continue;
            }

            if (! str_starts_with($token, '-') && $optionValue) {
                $optionValue = false;

                continue;
            }

            $this->arguments[] = $token;
            $this->tokens[]    = $token;
        }
    }
}
