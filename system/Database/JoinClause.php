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

use Closure;
use CodeIgniter\Exceptions\InvalidArgumentException;

/**
 * Builds conditions for a JOIN ON clause.
 */
class JoinClause
{
    /**
     * @var list<string>
     */
    private array $conditions = [];

    private int $conditionCount = 0;

    /**
     * @var list<int>
     */
    private array $groupConditionCounts = [];

    private bool $groupStarted = false;

    /**
     * @param Closure(string, mixed, bool): string $setBind
     *
     * @internal This class is normally created by BaseBuilder::join().
     */
    public function __construct(
        private readonly BaseConnection $db,
        private readonly Closure $setBind,
        private readonly bool $escape,
    ) {
    }

    /**
     * Adds a column comparison to the JOIN ON clause.
     *
     * @param non-empty-string $first  First column name, optionally with comparison operator
     * @param non-empty-string $second Second column name
     * @param bool|null        $escape Whether to protect identifiers
     *
     * @throws InvalidArgumentException
     */
    public function on(string $first, string $second, ?bool $escape = null): static
    {
        return $this->onColumn($first, $second, 'AND ', $escape);
    }

    /**
     * Adds an OR column comparison to the JOIN ON clause.
     *
     * @param non-empty-string $first  First column name, optionally with comparison operator
     * @param non-empty-string $second Second column name
     * @param bool|null        $escape Whether to protect identifiers
     *
     * @throws InvalidArgumentException
     */
    public function orOn(string $first, string $second, ?bool $escape = null): static
    {
        return $this->onColumn($first, $second, 'OR ', $escape);
    }

    /**
     * Adds a value comparison to the JOIN ON clause.
     *
     * @param non-empty-string $key    Column name, optionally with comparison operator
     * @param mixed            $value  Value to bind
     * @param bool|null        $escape Whether to protect identifiers
     *
     * @throws InvalidArgumentException
     */
    public function where(string $key, mixed $value = null, ?bool $escape = null): static
    {
        return $this->whereHaving($key, $value, 'AND ', $escape);
    }

    /**
     * Adds an OR value comparison to the JOIN ON clause.
     *
     * @param non-empty-string $key    Column name, optionally with comparison operator
     * @param mixed            $value  Value to bind
     * @param bool|null        $escape Whether to protect identifiers
     *
     * @throws InvalidArgumentException
     */
    public function orWhere(string $key, mixed $value = null, ?bool $escape = null): static
    {
        return $this->whereHaving($key, $value, 'OR ', $escape);
    }

    /**
     * Starts a condition group.
     */
    public function groupStart(): static
    {
        return $this->groupStartPrepare();
    }

    /**
     * Starts a condition group, prefixed with OR.
     */
    public function orGroupStart(): static
    {
        return $this->groupStartPrepare('', 'OR ');
    }

    /**
     * Starts a condition group, prefixed with NOT.
     */
    public function notGroupStart(): static
    {
        return $this->groupStartPrepare('NOT ');
    }

    /**
     * Starts a condition group, prefixed with OR NOT.
     */
    public function orNotGroupStart(): static
    {
        return $this->groupStartPrepare('NOT ', 'OR ');
    }

    /**
     * Ends the current condition group.
     *
     * @throws InvalidArgumentException
     */
    public function groupEnd(): static
    {
        if ($this->groupConditionCounts === []) {
            throw new InvalidArgumentException('JoinClause groupEnd() called without a matching groupStart().');
        }

        $conditionCount = array_pop($this->groupConditionCounts);

        if ($conditionCount === 0) {
            throw new InvalidArgumentException('JoinClause groups must contain at least one condition.');
        }

        $this->conditions[] = ')';
        $this->groupStarted = false;
        $this->incrementConditionCount();

        return $this;
    }

    /**
     * Compiles the JOIN ON clause conditions.
     *
     * @internal
     *
     * @throws InvalidArgumentException
     */
    public function compile(): string
    {
        if ($this->groupConditionCounts !== []) {
            throw new InvalidArgumentException('JoinClause groups must be balanced.');
        }

        if ($this->conditionCount === 0) {
            throw new InvalidArgumentException('JoinClause must contain at least one condition.');
        }

        return ' ON ' . implode('', $this->conditions);
    }

    /**
     * @param non-empty-string $first
     * @param non-empty-string $second
     */
    private function onColumn(string $first, string $second, string $type, ?bool $escape): static
    {
        [$first, $operator] = $this->parseFirstColumn($first);
        $second             = trim($second);

        if ($first === '' || $second === '') {
            throw new InvalidArgumentException('JoinClause column comparisons expect $first and $second to be non-empty strings.');
        }

        $escape ??= $this->escape;

        if ($escape) {
            $first  = $this->db->protectIdentifiers($first, false, true);
            $second = $this->db->protectIdentifiers($second, false, true);
        }

        $this->appendCondition($this->prefix($type) . $first . ' ' . $operator . ' ' . $second);

        return $this;
    }

    private function whereHaving(string $key, mixed $value, string $type, ?bool $escape): static
    {
        $key = trim($key);

        if ($key === '') {
            throw new InvalidArgumentException('JoinClause value comparisons expect $key to be a non-empty string.');
        }

        $escape ??= $this->escape;

        if ($value !== null) {
            [$key, $operator] = $this->parseWhereKey($key);
            $bind             = ($this->setBind)($key, $value, $escape);
            $condition        = $this->protectIdentifier($key, $escape) . $operator . " :{$bind}:";
        } elseif (preg_match('/\s*(!=|<>|IS(?:\s+NOT)?)\s*$/i', $key, $match, PREG_OFFSET_CAPTURE) === 1) {
            $key       = substr($key, 0, $match[0][1]);
            $operator  = $match[1][0] === '=' || strcasecmp($match[1][0], 'IS') === 0 ? ' IS NULL' : ' IS NOT NULL';
            $condition = $this->protectIdentifier($key, $escape) . $operator;
        } else {
            $condition = $this->protectIdentifier($key, $escape) . ' IS NULL';
        }

        $this->appendCondition($this->prefix($type) . $condition);

        return $this;
    }

    private function groupStartPrepare(string $not = '', string $type = 'AND '): static
    {
        $this->conditions[]           = $this->prefix($type) . $not . '(';
        $this->groupConditionCounts[] = 0;
        $this->groupStarted           = true;

        return $this;
    }

    /**
     * @return array{string, string}
     */
    private function parseFirstColumn(string $first): array
    {
        $first = trim($first);

        if (preg_match('/\s*(!=|<>|<=|>=|=|<|>)\s*$/', $first, $match) === 1) {
            return [rtrim(substr($first, 0, -strlen($match[0]))), trim($match[1])];
        }

        return [$first, '='];
    }

    /**
     * @return array{string, string}
     */
    private function parseWhereKey(string $key): array
    {
        if (preg_match('/\s*(!=|<>|<=|>=|=|<|>)\s*$/', $key, $match) === 1) {
            return [rtrim(substr($key, 0, -strlen($match[0]))), ' ' . trim($match[1])];
        }

        return [$key, ' ='];
    }

    private function protectIdentifier(string $identifier, bool $escape): string
    {
        return $escape ? $this->db->protectIdentifiers($identifier) : $identifier;
    }

    private function prefix(string $type): string
    {
        if ($this->conditions === [] || $this->groupStarted) {
            $this->groupStarted = false;

            return '';
        }

        return ' ' . $type;
    }

    private function appendCondition(string $condition): void
    {
        $this->conditions[] = $condition;
        $this->incrementConditionCount();
    }

    private function incrementConditionCount(): void
    {
        if ($this->groupConditionCounts === []) {
            $this->conditionCount++;

            return;
        }

        $index = array_key_last($this->groupConditionCounts);
        $this->groupConditionCounts[$index]++;
    }
}
