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

namespace CodeIgniter\Publisher;

use RuntimeException;

/**
 * Replace Text Content
 *
 * @see \CodeIgniter\Publisher\ContentReplacerTest
 */
class ContentReplacer
{
    /**
     * Replace content
     *
     * @param array $replaces [search => replace]
     */
    public function replace(string $content, array $replaces): string
    {
        return strtr($content, $replaces);
    }

    /**
     * Add text
     *
     * @param string $text    Text to add.
     * @param string $pattern Regexp search pattern.
     * @param string $replace Regexp replacement including text to add.
     *
     * @return string|null Updated content, or null if not updated.
     */
    private function add(string $content, string $text, string $pattern, string $replace): ?string
    {
        $return = preg_match('/' . preg_quote($text, '/') . '/u', $content);

        if ($return === false) {
            // Regexp error.
            throw new RuntimeException('Regex error. PCRE error code: ' . preg_last_error());
        }

        if ($return === 1) {
            // It has already been updated.
            return null;
        }

        $return = preg_replace($pattern, $replace, $content);

        if ($return === null) {
            // Regexp error.
            throw new RuntimeException('Regex error. PCRE error code: ' . preg_last_error());
        }

        return $return;
    }

    /**
     * Add line after the line with the string
     *
     * @param string $content Whole content.
     * @param string $line    Line to add.
     * @param string $after   String to search.
     *
     * @return string|null Updated content, or null if not updated.
     */
    public function addAfter(string $content, string $line, string $after): ?string
    {
        $pattern = '/(.*)(\n[^\n]*?' . preg_quote($after, '/') . '[^\n]*?\n)/su';
        $replace = '$1$2' . $line . "\n";

        return $this->add($content, $line, $pattern, $replace);
    }

    /**
     * Add line before the line with the string
     *
     * @param string $content Whole content.
     * @param string $line    Line to add.
     * @param string $before  String to search.
     *
     * @return string|null Updated content, or null if not updated.
     */
    public function addBefore(string $content, string $line, string $before): ?string
    {
        $pattern = '/(\n)([^\n]*?' . preg_quote($before, '/') . ')(.*)/su';
        $replace = '$1' . $line . "\n" . '$2$3';

        return $this->add($content, $line, $pattern, $replace);
    }
}
