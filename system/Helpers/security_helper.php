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

// CodeIgniter Security Helpers

if (! function_exists('sanitize_filename')) {
    /**
     * Sanitize Filename
     *
     * Tries to sanitize filenames in order to prevent directory traversal attempts
     * and other security threats, which is particularly useful for files that
     * were supplied via user input.
     *
     * If it is acceptable for the user input to include relative paths,
     * e.g. file/in/some/approved/folder.txt, you can set the second optional
     * parameter, $relativePath to TRUE.
     * Only forward slashes are preserved, backslashes are always removed.
     *
     * @param string $filename     Input file name
     * @param bool   $relativePath Whether to preserve paths
     */
    function sanitize_filename(string $filename, bool $relativePath = false): string
    {
        // List of sanitized filename strings
        $bad = [
            '\\',
            '../',
            '<!--',
            '-->',
            '<',
            '>',
            "'",
            '"',
            '&',
            '$',
            '#',
            '{',
            '}',
            '[',
            ']',
            '=',
            ';',
            '?',
            '%20',
            '%22',
            '%3c',
            '%253c',
            '%3e',
            '%0e',
            '%28',
            '%29',
            '%2528',
            '%26',
            '%24',
            '%3f',
            '%3b',
            '%3d',
        ];

        if (! $relativePath) {
            $bad[] = './';
            $bad[] = '/';
        }

        $filename = remove_invisible_characters($filename, false);

        do {
            $old      = $filename;
            $filename = str_replace($bad, '', $filename);
        } while ($old !== $filename);

        return $filename;
    }
}

if (! function_exists('strip_image_tags')) {
    /**
     * Replaces image tags with their raw src values.
     *
     * If tag replacement fails or the iteration limit is reached, remaining
     * "<img" prefixes are escaped as "&lt;img".
     *
     * This function does not sanitize HTML. Escape the result appropriately
     * when displaying it as text.
     */
    function strip_image_tags(string $str): string
    {
        if (! str_contains(strtolower($str), strtolower('<img'))) {
            return $str;
        }

        // HTML whitespace excludes vertical tabs. Names may start with "="
        // and contain quotes; quoted values begin only after an assignment.
        $whitespace = '[\t\n\f\r ]';
        $separators = '[\t\n\f\r /]';
        $name       = '[^\t\n\f\r />][^\t\n\f\r />=]*+';
        $unquoted   = '[^\t\n\f\r >"\'][^\t\n\f\r >]*+';
        $value      = '(?:"[^"]*+(?:"|\z)|\'[^\']*+(?:\'|\z)|' . $unquoted . ')';
        $assignment = '(?:' . $whitespace . '*+=' . $whitespace . '*+(?:' . $value . ')?)?';
        $attribute  = $name . $assignment;

        $tagPattern = '#<img(?=[\t\n\f\r />]|\z)((?:' . $separators . '++|' . $attribute . ')*+)(>|\z)#i';

        // Skip complete non-src attributes, then capture the first src value.
        // Possessive repetition prevents searching inside earlier attributes.
        $nameEnd    = '(?=[\t\n\f\r />=]|\z)';
        $srcPattern = '#\A(?:' . $separators . '*+(?!src' . $nameEnd . ')' . $attribute . ')*+'
            . $separators . '*+src' . $nameEnd
            . '(?:' . $whitespace . '*+=' . $whitespace . '*+'
            . '(?|"([^"]*+)"|\'([^\']*+)\'|(' . $unquoted . '))?)?#i';

        $replace = static function (array $tag) use ($srcPattern): string {
            // Browsers discard a tag that reaches EOF before its closing ">".
            if ($tag[2] === '') {
                return '';
            }

            if (preg_match($srcPattern, $tag[1], $src) !== 1) {
                return '';
            }

            return $src[1] ?? '';
        };

        // Removing a tag may join surrounding text into another image tag.
        for ($remaining = 10; $remaining > 0; $remaining--) {
            $result = preg_replace_callback($tagPattern, $replace, $str);

            if ($result === null) {
                break;
            }

            if ($result === $str) {
                return $result;
            }

            $str = $result;

            if ($remaining > 1 && ! str_contains(strtolower($str), strtolower('<img'))) {
                return $str;
            }
        }

        // Tag replacement failed or the iteration limit was reached.
        return str_ireplace('<img', '&lt;img', $str);
    }
}

if (! function_exists('encode_php_tags')) {
    /**
     * Convert PHP tags to entities
     */
    function encode_php_tags(string $str): string
    {
        return str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $str);
    }
}
