<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

/**
 * Query builder
 */
class Query implements QueryInterface
{
    /**
     * The query string, as provided by the user.
     *
     * @var string
     */
    protected $originalQueryString;

    /**
     * The final query string after binding, etc.
     *
     * @var string
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

    public function __construct(ConnectionInterface &$db)
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

        if ($binds !== null) {
            if (! is_array($binds)) {
                $binds = [$binds];
            }

            if ($setEscape) {
                array_walk($binds, static function (&$item) {
                    $item = [
                        $item,
                        true,
                    ];
                });
            }
            $this->binds = $binds;
        }

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
            array_walk($binds, static function (&$item) {
                $item = [$item, true];
            });
        }

        $this->binds = $binds;

        return $this;
    }

    /**
     * Returns the final, processed query string after binding, etal
     * has been performed.
     */
    public function getQuery(): string
    {
        if (empty($this->finalQueryString)) {
            $this->finalQueryString = $this->originalQueryString;
        }

        $this->compileBinds();

        return $this->finalQueryString;
    }

    /**
     * Records the execution time of the statement using microtime(true)
     * for it's start and end values. If no end value is present, will
     * use the current time to determine total duration.
     *
     * @param float $end
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
        $sql = empty($this->finalQueryString) ? $this->originalQueryString : $this->finalQueryString;

        $this->finalQueryString = preg_replace('/(\W)' . $orig . '(\S+?)/', '\\1' . $swap . '\\2', $sql);

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
     * Escapes and inserts any binds into the finalQueryString object.
     *
     * @see https://regex101.com/r/EUEhay/4
     */
    protected function compileBinds()
    {
        $sql = $this->finalQueryString;

        $hasNamedBinds = preg_match('/:((?!=).+):/', $sql) === 1;

        if (empty($this->binds)
            || empty($this->bindMarker)
            || (! $hasNamedBinds && strpos($sql, $this->bindMarker) === false)
        ) {
            return;
        }

        if (! is_array($this->binds)) {
            $binds     = [$this->binds];
            $bindCount = 1;
        } else {
            $binds     = $this->binds;
            $bindCount = count($binds);
        }

        // Reverse the binds so that duplicate named binds
        // will be processed prior to the original binds.
        if (! is_numeric(key(array_slice($binds, 0, 1)))) {
            $binds = array_reverse($binds);
        }

        $ml  = strlen($this->bindMarker);
        $sql = $hasNamedBinds ? $this->matchNamedBinds($sql, $binds) : $this->matchSimpleBinds($sql, $binds, $bindCount, $ml);

        $this->finalQueryString = $sql;
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
        if ($c = preg_match_all("/'[^']*'/", $sql, $matches)) {
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

            $sql = substr_replace($sql, $escapedValue, $matches[0][$c][1], $ml);
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
            'SELECT',
            'DISTINCT',
            'FROM',
            'WHERE',
            'AND',
            'LEFT&nbsp;JOIN',
            'RIGHT&nbsp;JOIN',
            'JOIN',
            'ORDER&nbsp;BY',
            'GROUP&nbsp;BY',
            'LIMIT',
            'INSERT',
            'INTO',
            'VALUES',
            'UPDATE',
            'OR&nbsp;',
            'HAVING',
            'OFFSET',
            'NOT&nbsp;IN',
            'IN',
            'LIKE',
            'NOT&nbsp;LIKE',
            'COUNT',
            'MAX',
            'MIN',
            'ON',
            'AS',
            'AVG',
            'SUM',
            '(',
            ')',
        ];

        if (empty($this->finalQueryString)) {
            $this->compileBinds(); // @codeCoverageIgnore
        }

        $sql = $this->finalQueryString;

        foreach ($highlight as $term) {
            $sql = str_replace($term, '<strong>' . $term . '</strong>', $sql);
        }

        return $sql;
    }

    /**
     * Return text representation of the query
     */
    public function __toString(): string
    {
        return $this->getQuery();
    }
}
