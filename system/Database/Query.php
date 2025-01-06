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

    /**
     * Sets the raw query string to use for this statement.
     *
     * @param mixed $binds
     *
     * @return $this
     */
    public function setQuery(string $sql, $binds = null, bool $setEscape = true)
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

    /**
     * Returns the final, processed query string after binding, etal
     * has been performed.
     */
    public function getQuery(): string
    {
        if (empty($this->finalQueryString)) {
            $this->compileBinds();
        }

        return $this->finalQueryString;
    }

    /**
     * Records the execution time of the statement using microtime(true)
     * for it's start and end values. If no end value is present, will
     * use the current time to determine total duration.
     *
     * @return $this
     */
    public function setDuration(float $start, ?float $end = null)
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

    /**
     * Returns the duration of this query during execution, or null if
     * the query has not been executed yet.
     *
     * @param int $decimals The accuracy of the returned time.
     */
    public function getDuration(int $decimals = 6): string
    {
        return number_format(($this->endTime - $this->startTime), $decimals);
    }

    /**
     * Stores the error description that happened for this query.
     *
     * @return $this
     */
    public function setError(int $code, string $error)
    {
        $this->errorCode   = $code;
        $this->errorString = $error;

        return $this;
    }

    /**
     * Reports whether this statement created an error not.
     */
    public function hasError(): bool
    {
        return ! empty($this->errorString);
    }

    /**
     * Returns the error code created while executing this statement.
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Returns the error message created while executing this statement.
     */
    public function getErrorMessage(): string
    {
        return $this->errorString;
    }

    /**
     * Determines if the statement is a write-type query or not.
     */
    public function isWriteType(): bool
    {
        return $this->db->isWriteType($this->originalQueryString);
    }

    /**
     * Swaps out one table prefix for a new one.
     *
     * @return $this
     */
    public function swapPrefix(string $orig, string $swap)
    {
        $sql = $this->swappedQueryString ?? $this->originalQueryString;

        $from = '/(\W)' . $orig . '(\S)/';
        $to   = '\\1' . $swap . '\\2';

        $this->swappedQueryString = preg_replace($from, $to, $sql);

        unset($this->finalQueryString);

        return $this;
    }

    /**
     * Returns the original SQL that was passed into the system.
     */
    public function getOriginalQuery(): string
    {
        return $this->originalQueryString;
    }

    /**
     * Escapes and inserts any binds into the finalQueryString property.
     *
     * @see https://regex101.com/r/EUEhay/5
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

    /**
     * Match bindings
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

            $replacers[":{$placeholder}:"] = $escapedValue;
        }

        return strtr($sql, $replacers);
    }

    /**
     * Match bindings
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
