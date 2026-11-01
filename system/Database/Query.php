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

namespace CodeIgniter\Database;

use CodeIgniter\Database\MySQLi\Connection as MySQLiConnection;
use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Database\Postgre\Connection as PostgreConnection;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use CodeIgniter\Database\SQLSRV\Connection as SQLSRVConnection;
use Stringable;

/**
 * Query builder
 */
class Query implements QueryInterface, Stringable
{
    /**
     * The query string, as provided by the user.
     *
     * @var string
     */
    protected $originalQueryString;

    /**
     * The query string if table prefix has been swapped.
     *
     * @var string|null
     */
    protected $swappedQueryString;

    /**
     * The final query string after binding, etc.
     *
     * @var string|null
     */
    protected $finalQueryString;

    /**
     * The binds and their values used for binding.
     *
     * @var array<array-key, array{0: mixed, 1: bool}>
     */
    protected $binds = [];

    /**
     * Bind marker
     *
     * Character used to identify values in a prepared statement.
     *
     * @var string
     */
    protected $bindMarker = '?';

    /**
     * The start time in seconds with microseconds
     * for when this query was executed.
     *
     * @var float|string
     */
    protected $startTime;

    /**
     * The end time in seconds with microseconds
     * for when this query was executed.
     *
     * @var float
     */
    protected $endTime;

    /**
     * The error code, if any.
     *
     * @var int
     */
    protected $errorCode;

    /**
     * The error message, if any.
     *
     * @var string
     */
    protected $errorString = '';

    /**
     * Pointer to database connection.
     * Mainly for escaping features.
     *
     * @var ConnectionInterface
     */
    public $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function setQuery(string $sql, mixed $binds = null, bool $setEscape = true): self
    {
        $this->originalQueryString = $sql;
        unset($this->swappedQueryString);

        if ($binds !== null) {
            if (! is_array($binds)) {
                $binds = [$binds];
            }

            if ($setEscape) {
                array_walk($binds, static function (&$item): void {
                    $item = [
                        $item,
                        true,
                    ];
                });
            }
            $this->binds = $binds;
        }

        unset($this->finalQueryString);

        return $this;
    }

    /**
     * Will store the variables to bind into the query later.
     *
     * @param array<array-key, mixed> $binds
     *
     * @return $this
     */
    public function setBinds(array $binds, bool $setEscape = true)
    {
        if ($setEscape) {
            array_walk($binds, static function (&$item): void {
                $item = [$item, true];
            });
        }

        $this->binds = $binds;

        unset($this->finalQueryString);

        return $this;
    }

    public function getQuery(): string
    {
        if (! isset($this->finalQueryString) || $this->finalQueryString === '') {
            $this->compileBinds();
        }

        return $this->finalQueryString;
    }

    public function setDuration(float $start, ?float $end = null): self
    {
        $this->startTime = $start;

        $end ??= microtime(true);

        $this->endTime = $end;

        return $this;
    }

    /**
     * Returns the start time in seconds with microseconds.
     *
     * @return float|string
     */
    public function getStartTime(bool $returnRaw = false, int $decimals = 6)
    {
        if ($returnRaw) {
            return $this->startTime;
        }

        return number_format($this->startTime, $decimals);
    }

    public function getDuration(int $decimals = 6): string
    {
        return number_format(($this->endTime - $this->startTime), $decimals);
    }

    public function setError(int $code, string $error): self
    {
        $this->errorCode   = $code;
        $this->errorString = $error;

        return $this;
    }

    public function hasError(): bool
    {
        return $this->errorString !== '';
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorString;
    }

    public function isWriteType(): bool
    {
        return $this->db->isWriteType($this->originalQueryString);
    }

    public function swapPrefix(string $orig, string $swap): self
    {
        $sql = $this->swappedQueryString ?? $this->originalQueryString;

        $from = '/(\W)' . $orig . '(\S)/';
        $to   = '\\1' . $swap . '\\2';

        $this->swappedQueryString = preg_replace($from, $to, $sql);

        unset($this->finalQueryString);

        return $this;
    }

    public function getOriginalQuery(): string
    {
        return $this->originalQueryString;
    }

    /**
     * Escapes and inserts any binds into the finalQueryString property.
     *
     * @see https://regex101.com/r/EUEhay/5
     *
     * @return void
     */
    protected function compileBinds()
    {
        $sql   = $this->swappedQueryString ?? $this->originalQueryString;
        $binds = $this->binds;

        if ($binds === []) {
            $this->finalQueryString = $sql;

            return;
        }

        if (is_int(array_key_first($binds))) {
            $bindCount = count($binds);
            $ml        = strlen($this->bindMarker);

            $this->finalQueryString = $this->matchSimpleBinds($sql, $binds, $bindCount, $ml);
        } else {
            // Reverse the binds so that duplicate named binds
            // will be processed prior to the original binds.
            $binds = array_reverse($binds);

            $this->finalQueryString = $this->matchNamedBinds($sql, $binds);
        }
    }

    /**
     * @param array<array-key, array{0: mixed, 1: bool}> $binds
     */
    protected function matchNamedBinds(string $sql, array $binds): string
    {
        $replacers = [];

        foreach ($binds as $placeholder => $value) {
            // $value[1] contains the boolean whether should be escaped or not
            $escapedValue = $value[1] ? $this->db->escape($value[0]) : $value[0];

            // In order to correctly handle backlashes in saved strings
            // we will need to preg_quote, so remove the wrapping escape characters
            // otherwise it will get escaped.
            if (is_array($value[0])) {
                $escapedValue = '(' . implode(',', $escapedValue) . ')';
            }

            $replacers[":{$placeholder}:"] = (string) $escapedValue;
        }

        return $this->replaceNamedPlaceholders($sql, $replacers);
    }

    /**
     * Works like strtr(), but leaves quoted strings, quoted identifiers
     * and comments untouched, so that values already rendered into the SQL
     * (escaped values, compiled subqueries) are never treated as placeholders.
     *
     * @param array<string, string> $replacers
     */
    private function replaceNamedPlaceholders(string $sql, array $replacers): string
    {
        if (strpbrk($sql, '\'"`#[$') === false && ! str_contains($sql, '--') && ! str_contains($sql, '/*')) {
            return strtr($sql, $replacers);
        }

        $maxLength = max(array_map(strlen(...), array_keys($replacers)));
        $isMySQL   = $this->db instanceof MySQLiConnection;
        $isSQLSRV  = $this->db instanceof SQLSRVConnection;
        $isPostgre = $this->db instanceof PostgreConnection;
        $isSQLite  = $this->db instanceof SQLite3Connection;
        $isOCI8    = $this->db instanceof OCI8Connection;

        // Backslash escapes matter only when there is a backslash in the SQL.
        $backslashEscapes = str_contains($sql, '\\')
            && $this->db instanceof BaseConnection
            && $this->db->escapeString('\\') !== '\\';

        // Multibyte characters matter only when there is a non-ASCII byte in the SQL.
        $multibyte = preg_match('/[\x80-\xff]/', $sql) === 1
            ? $this->getMultibyteCharPattern($isMySQL, $isPostgre)
            : null;

        $stop         = ':\'"`-/#[$' . ($multibyte === null ? '' : implode('', array_map(chr(...), range(0x80, 0xFF))));
        $multibyteEnd = -1;
        $length       = strlen($sql);
        $result       = '';
        $offset       = 0;

        while ($offset < $length) {
            $position = $offset + strcspn($sql, $stop, $offset);
            $result .= substr($sql, $offset, $position - $offset);

            if ($position >= $length) {
                break;
            }

            $char = $sql[$position];
            $next = $sql[$position + 1] ?? '';
            $end  = $position + 1;

            if ($multibyte !== null && ord($char) >= 0x80) {
                $end          = $position + $this->getCharLength($sql, $position, $multibyte);
                $multibyteEnd = $end;
            } elseif ($char === ':') {
                $placeholder = $this->findPlaceholderAt($sql, $position, $replacers, $maxLength);

                if ($placeholder !== null) {
                    $result .= $replacers[$placeholder];
                    $offset = $position + strlen($placeholder);

                    continue;
                }
            } elseif (
                $char === '\'' && $isOCI8
                && preg_match('/(?<![\w$#])[nN]?[qQ]\z/', substr($sql, max(0, $position - 3), min(3, $position))) === 1
                && preg_match('/\S/', $sql[$position + 1] ?? ' ') === 1
            ) {
                // Oracle alternative quoting: q'[...]', q'{...}', q'<...>', q'(...)' or q'X...X'
                $close = strpos($sql, strtr($sql[$position + 1], '[{<(', ']}>)') . '\'', $position + 2);
                $end   = $close === false ? $length : $close + 2;
            } elseif ($char === '\'') {
                // PostgreSQL E'...' strings always use backslash escapes.
                $isEscapeString = $isPostgre
                    && $position > 0
                    && $multibyteEnd !== $position
                    && ($sql[$position - 1] === 'E' || $sql[$position - 1] === 'e')
                    && ($position === 1 || preg_match('/[\w$\x80-\xff]/', $sql[$position - 2]) !== 1);

                $end = $this->findQuotedEnd($sql, $position, '\'', $backslashEscapes || $isEscapeString, $multibyte);
            } elseif ($char === '"') {
                // Only MySQL uses "..." for strings; elsewhere it quotes
                // identifiers, in which a backslash is never an escape.
                $end = $this->findQuotedEnd($sql, $position, '"', $backslashEscapes && $isMySQL, $multibyte);
            } elseif ($char === '`') {
                $end = $this->findQuotedEnd($sql, $position, '`', false);
            } elseif ($char === '[' && ($isSQLSRV || $isSQLite)) {
                $end = $this->findQuotedEnd($sql, $position, ']', false);
            } elseif (
                $char === '$' && $isPostgre
                && ($position === 0 || preg_match('/[\w$\x80-\xff]/', $sql[$position - 1]) !== 1)
                && preg_match('/\G\$(?:[A-Za-z_\x80-\xff][\w\x80-\xff]*)?\$/', $sql, $match, 0, $position) === 1
            ) {
                // PostgreSQL dollar-quoted strings: $$...$$ or $tag$...$tag$
                $close = strpos($sql, $match[0], $position + strlen($match[0]));
                $end   = $close === false ? $length : $close + strlen($match[0]);
            } elseif (
                ($char === '-' && $next === '-' && (! $isMySQL || ord($sql[$position + 2] ?? ' ') <= 32))
                || ($char === '#' && $isMySQL)
            ) {
                // MySQL requires whitespace after "--" and also supports "#" comments.
                $newLine = strpos($sql, "\n", $position);
                $end     = $newLine === false ? $length : $newLine;
            } elseif ($char === '/' && $next === '*' && (! $isMySQL || ($sql[$position + 2] ?? '') !== '!')) {
                // MySQL executes /*! ... */ comments, so they are not skipped.
                // PostgreSQL and SQL Server allow nested block comments.
                $end = $this->findCommentEnd($sql, $position, $isPostgre || $isSQLSRV);
            }

            $result .= substr($sql, $position, $end - $position);
            $offset = $end;
        }

        return $result;
    }

    /**
     * Returns the longest placeholder that starts at the given position,
     * matching the strtr() behavior.
     *
     * @param array<string, string> $replacers
     */
    private function findPlaceholderAt(string $sql, int $position, array $replacers, int $maxLength): ?string
    {
        $candidates = [];
        $colon      = $position;

        while (($colon = strpos($sql, ':', $colon + 1)) !== false && $colon - $position < $maxLength) {
            $candidates[] = substr($sql, $position, $colon - $position + 1);
        }

        foreach (array_reverse($candidates) as $candidate) {
            if (array_key_exists($candidate, $replacers)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Returns a pattern matching one multibyte character for connection
     * charsets in which a trailing byte may be an ASCII byte, such as
     * a backslash, or null when the charset does not need it.
     */
    private function getMultibyteCharPattern(bool $isMySQL, bool $isPostgre): ?string
    {
        if (! $this->db instanceof BaseConnection) {
            return null;
        }

        // Same rules as my_ismbchar() in MySQL: invalid sequences are single bytes.
        if ($isMySQL) {
            return match (strtolower($this->db->charset)) {
                'big5'          => '/\G[\xa1-\xf9][\x40-\x7e\xa1-\xfe]/',
                'gbk'           => '/\G[\x81-\xfe][\x40-\x7e\x80-\xfe]/',
                'gb18030'       => '/\G[\x81-\xfe](?:[\x30-\x39][\x81-\xfe][\x30-\x39]|[\x40-\x7e\x80-\xfe])/',
                'cp932', 'sjis' => '/\G[\x81-\x9f\xe0-\xfc][\x40-\x7e\x80-\xfc]/',
                default         => null,
            };
        }

        // Same rules as pg_encoding_mblen() in libpq; PostgreSQL ignores case
        // and non-alphanumeric characters in encoding names.
        if ($isPostgre) {
            return match (strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $this->db->charset))) {
                'big5', 'win950', 'windows950', 'gbk', 'win936', 'windows936'         => '/\G[\x80-\xff][\x00-\xff]/',
                'gb18030'                                                             => '/\G[\x80-\xff](?:[\x30-\x39][\x00-\xff]{2}|[\x00-\xff])/',
                'mskanji', 'shiftjis', 'shiftjis2004', 'sjis', 'win932', 'windows932' => '/\G[\x80-\xa0\xe0-\xff][\x00-\xff]/',
                default                                                               => null,
            };
        }

        return null;
    }

    /**
     * Returns the length in bytes of the character at the given position.
     */
    private function getCharLength(string $sql, int $position, string $multibyte): int
    {
        return preg_match($multibyte, $sql, $match, 0, $position) === 1 ? strlen($match[0]) : 1;
    }

    /**
     * Returns the position just after the end of the block comment
     * that starts at the given position.
     */
    private function findCommentEnd(string $sql, int $position, bool $nested): int
    {
        if (! $nested) {
            $close = strpos($sql, '*/', $position + 2);

            return $close === false ? strlen($sql) : $close + 2;
        }

        $depth  = 0;
        $offset = $position;

        while (preg_match('~/\*|\*/~', $sql, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $offset = $match[0][1] + 2;
            $depth += $match[0][0] === '/*' ? 1 : -1;

            if ($depth === 0) {
                return $offset;
            }
        }

        return strlen($sql);
    }

    /**
     * Returns the position just after the closing quote of the quoted
     * string or identifier that starts at the given position.
     */
    private function findQuotedEnd(string $sql, int $position, string $quote, bool $backslashEscapes, ?string $multibyte = null): int
    {
        $stop   = $backslashEscapes ? $quote . '\\' : $quote;
        $length = strlen($sql);
        $offset = $position + 1;

        if ($multibyte !== null) {
            $stop .= implode('', array_map(chr(...), range(0x80, 0xFF)));
        }

        while ($offset < $length) {
            $offset += strcspn($sql, $stop, $offset);

            if ($offset >= $length) {
                break;
            }

            if ($multibyte !== null && ord($sql[$offset]) >= 0x80) {
                $offset += $this->getCharLength($sql, $offset, $multibyte);

                continue;
            }

            if ($sql[$offset] === '\\') {
                $offset += 2;

                continue;
            }

            if (($sql[$offset + 1] ?? '') === $quote) {
                $offset += 2;

                continue;
            }

            return $offset + 1;
        }

        return $length;
    }

    /**
     * @param array<array-key, array{0: mixed, 1: bool}> $binds
     */
    protected function matchSimpleBinds(string $sql, array $binds, int $bindCount, int $ml): string
    {
        if ($c = preg_match_all("/'[^']*'/", $sql, $matches) >= 1) {
            $c = preg_match_all('/' . preg_quote($this->bindMarker, '/') . '/i', str_replace($matches[0], str_replace($this->bindMarker, str_repeat(' ', $ml), $matches[0]), $sql, $c), $matches, PREG_OFFSET_CAPTURE);

            // Bind values' count must match the count of markers in the query
            if ($bindCount !== $c) {
                return $sql;
            }
        } elseif (($c = preg_match_all('/' . preg_quote($this->bindMarker, '/') . '/i', $sql, $matches, PREG_OFFSET_CAPTURE)) !== $bindCount) {
            return $sql;
        }

        do {
            $c--;
            $escapedValue = $binds[$c][1] ? $this->db->escape($binds[$c][0]) : $binds[$c][0];

            if (is_array($escapedValue)) {
                $escapedValue = '(' . implode(',', $escapedValue) . ')';
            }

            $sql = substr_replace($sql, (string) $escapedValue, $matches[0][$c][1], $ml);
        } while ($c !== 0);

        return $sql;
    }

    /**
     * Returns string to display in debug toolbar
     */
    public function debugToolbarDisplay(): string
    {
        // Key words we want bolded
        static $highlight = [
            'AND',
            'AS',
            'ASC',
            'AVG',
            'BY',
            'COUNT',
            'DESC',
            'DISTINCT',
            'FROM',
            'GROUP',
            'HAVING',
            'IN',
            'INNER',
            'INSERT',
            'INTO',
            'IS',
            'JOIN',
            'LEFT',
            'LIKE',
            'LIMIT',
            'MAX',
            'MIN',
            'NOT',
            'NULL',
            'OFFSET',
            'ON',
            'OR',
            'ORDER',
            'RIGHT',
            'SELECT',
            'SUM',
            'UPDATE',
            'VALUES',
            'WHERE',
        ];

        $sql = esc($this->getQuery());

        /**
         * @see https://stackoverflow.com/a/20767160
         * @see https://regex101.com/r/hUlrGN/4
         */
        $search = '/\b(?:' . implode('|', $highlight) . ')\b(?![^(&#039;)]*&#039;(?:(?:[^(&#039;)]*&#039;){2})*[^(&#039;)]*$)/';

        return preg_replace_callback($search, static fn ($matches): string => '<strong>' . str_replace(' ', '&nbsp;', $matches[0]) . '</strong>', $sql);
    }

    /**
     * Return text representation of the query
     */
    public function __toString(): string
    {
        return $this->getQuery();
    }
}
