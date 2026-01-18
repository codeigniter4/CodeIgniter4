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
     * @var array
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
    protected $errorString;

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
        if (empty($this->finalQueryString)) {
            $this->compileBinds();
        }

        return $this->finalQueryString;
    }

    public function setDuration(float $start, ?float $end = null): self
    {
        $this->startTime = $start;

        if ($end === null) {
            $end = microtime(true);
        }

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
        return ! empty($this->errorString);
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

        if (empty($binds)) {
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

            $replacers[":{$placeholder}:"] = $escapedValue;
        }

        return strtr($sql, $replacers);
    }

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
