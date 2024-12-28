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
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Traits\ConditionalTrait;
use Config\Feature;
use InvalidArgumentException;

/**
 * Class BaseBuilder
 *
 * Provides the core Query Builder methods.
 * Database-specific Builders might need to override
 * certain methods to make them work.
 */
class BaseBuilder
{
    use ConditionalTrait;

    /**
     * Reset DELETE data flag
     *
     * @var bool
     */
    protected $resetDeleteData = false;

    /**
     * QB SELECT data
     *
     * @var array
     */
    protected $QBSelect = [];

    /**
     * QB DISTINCT flag
     *
     * @var bool
     */
    protected $QBDistinct = false;

    /**
     * QB FROM data
     *
     * @var array
     */
    protected $QBFrom = [];

    /**
     * QB JOIN data
     *
     * @var array
     */
    protected $QBJoin = [];

    /**
     * QB WHERE data
     *
     * @var array
     */
    protected $QBWhere = [];

    /**
     * QB GROUP BY data
     *
     * @var array
     */
    public $QBGroupBy = [];

    /**
     * QB HAVING data
     *
     * @var array
     */
    protected $QBHaving = [];

    /**
     * QB keys
     * list of column names.
     *
     * @var list<string>
     */
    protected $QBKeys = [];

    /**
     * QB LIMIT data
     *
     * @var bool|int
     */
    protected $QBLimit = false;

    /**
     * QB OFFSET data
     *
     * @var bool|int
     */
    protected $QBOffset = false;

    /**
     * QB ORDER BY data
     *
     * @var array|string|null
     */
    public $QBOrderBy = [];

    /**
     * QB UNION data
     *
     * @var list<string>
     */
    protected array $QBUnion = [];

    /**
     * Whether to protect identifiers in SELECT
     *
     * @var list<bool|null> true=protect, false=not protect
     */
    public $QBNoEscape = [];

    /**
     * QB data sets
     *
     * @var array<string, string>|list<list<int|string>>
     */
    protected $QBSet = [];

    /**
     * QB WHERE group started flag
     *
     * @var bool
     */
    protected $QBWhereGroupStarted = false;

    /**
     * QB WHERE group count
     *
     * @var int
     */
    protected $QBWhereGroupCount = 0;

    /**
     * Ignore data that cause certain
     * exceptions, for example in case of
     * duplicate keys.
     *
     * @var bool
     */
    protected $QBIgnore = false;

    /**
     * QB Options data
     * Holds additional options and data used to render SQL
     * and is reset by resetWrite()
     *
     * @var array{
     *   updateFieldsAdditional?: array,
     *   tableIdentity?: string,
     *   updateFields?: array,
     *   constraints?: array,
     *   setQueryAsData?: string,
     *   sql?: string,
     *   alias?: string,
     *   fieldTypes?: array<string, array<string, string>>
     * }
     *
     * fieldTypes: [ProtectedTableName => [FieldName => Type]]
     */
    protected $QBOptions;

    /**
     * A reference to the database connection.
     *
     * @var BaseConnection
     */
    protected $db;

    /**
     * Name of the primary table for this instance.
     * Tracked separately because $QBFrom gets escaped
     * and prefixed.
     *
     * When $tableName to the constructor has multiple tables,
     * the value is empty string.
     *
     * @var string
     */
    protected $tableName;

    /**
     * ORDER BY random keyword
     *
     * @var array
     */
    protected $randomKeyword = [
        'RAND()',
        'RAND(%d)',
    ];

    /**
     * COUNT string
     *
     * @used-by CI_DB_driver::count_all()
     * @used-by BaseBuilder::count_all_results()
     *
     * @var string
     */
    protected $countString = 'SELECT COUNT(*) AS ';

    /**
     * Collects the named parameters and
     * their values for later binding
     * in the Query object.
     *
     * @var array
     */
    protected $binds = [];

    /**
     * Collects the key count for named parameters
     * in the Query object.
     *
     * @var array
     */
    protected $bindsKeyCount = [];

    /**
     * Some databases, like SQLite, do not by default
     * allow limiting of delete clauses.
     *
     * @var bool
     */
    protected $canLimitDeletes = true;

    /**
     * Some databases do not by default
     * allow limit update queries with WHERE.
     *
     * @var bool
     */
    protected $canLimitWhereUpdates = true;

    /**
     * Specifies which sql statements
     * support the ignore option.
     *
     * @var array
     */
    protected $supportedIgnoreStatements = [];

    /**
     * Builder testing mode status.
     *
     * @var bool
     */
    protected $testMode = false;

    /**
     * Tables relation types
     *
     * @var array
     */
    protected $joinTypes = [
        'LEFT',
        'RIGHT',
        'OUTER',
        'INNER',
        'LEFT OUTER',
        'RIGHT OUTER',
    ];

    /**
     * Strings that determine if a string represents a literal value or a field name
     *
     * @var list<string>
     */
    protected $isLiteralStr = [];

    /**
     * RegExp used to get operators
     *
     * @var list<string>
     */
    protected $pregOperators = [];

    /**
     * Constructor
     *
     * @param array|string $tableName tablename or tablenames with or without aliases
     *
     * Examples of $tableName: `mytable`, `jobs j`, `jobs j, users u`, `['jobs j','users u']`
     *
     * @throws DatabaseException
     */
    public function __construct($tableName, ConnectionInterface $db, ?array $options = null)
    {
        if (empty($tableName)) {
            throw new DatabaseException('A table must be specified when creating a new Query Builder.');
        }

        /**
         * @var BaseConnection $db
         */
        $this->db = $db;

        // If it contains `,`, it has multiple tables
        if (is_string($tableName) && ! str_contains($tableName, ',')) {
            $this->tableName = $tableName;  // @TODO remove alias if exists
        } else {
            $this->tableName = '';
        }

        $this->from($tableName);

        if ($options !== null && $options !== []) {
            foreach ($options as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * Returns the current database connection
     *
     * @return BaseConnection
     */
    public function db(): ConnectionInterface
    {
        return $this->db;
    }

    /**
     * Sets a test mode status.
     *
     * @return $this
     */
    public function testMode(bool $mode = true)
    {
        $this->testMode = $mode;

        return $this;
    }

    /**
     * Gets the name of the primary table.
     */
    public function getTable(): string
    {
        return $this->tableName;
    }

    /**
     * Returns an array of bind values and their
     * named parameters for binding in the Query object later.
     */
    public function getBinds(): array
    {
        return $this->binds;
    }

    /**
     * Ignore
     *
     * Set ignore Flag for next insert,
     * update or delete query.
     *
     * @return $this
     */
    public function ignore(bool $ignore = true)
    {
        $this->QBIgnore = $ignore;

        return $this;
    }

    /**
     * Generates the SELECT portion of the query
     *
     * @param list<RawSql|string>|RawSql|string $select
     * @param bool|null                         $escape Whether to protect identifiers
     *
     * @return $this
     */
    public function select($select = '*', ?bool $escape = null)
    {
        // If the escape value was not set, we will base it on the global setting
        if (! is_bool($escape)) {
            $escape = $this->db->protectIdentifiers;
        }

        if ($select instanceof RawSql) {
            $select = [$select];
        }

        if (is_string($select)) {
            $select = ($escape === false) ? [$select] : explode(',', $select);
        }

        foreach ($select as $val) {
            if ($val instanceof RawSql) {
                $this->QBSelect[]   = $val;
                $this->QBNoEscape[] = false;

                continue;
            }

            $val = trim($val);

            if ($val !== '') {
                $this->QBSelect[] = $val;

                /*
                 * When doing 'SELECT NULL as field_alias FROM table'
                 * null gets taken as a field, and therefore escaped
                 * with backticks.
                 * This prevents NULL being escaped
                 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1169
                 */
                if (mb_stripos($val, 'NULL') === 0) {
                    $this->QBNoEscape[] = false;

                    continue;
                }

                $this->QBNoEscape[] = $escape;
            }
        }

        return $this;
    }

    /**
     * Generates a SELECT MAX(field) portion of a query
     *
     * @return $this
     */
    public function selectMax(string $select = '', string $alias = '')
    {
        return $this->maxMinAvgSum($select, $alias);
    }

    /**
     * Generates a SELECT MIN(field) portion of a query
     *
     * @return $this
     */
    public function selectMin(string $select = '', string $alias = '')
    {
        return $this->maxMinAvgSum($select, $alias, 'MIN');
    }

    /**
     * Generates a SELECT AVG(field) portion of a query
     *
     * @return $this
     */
    public function selectAvg(string $select = '', string $alias = '')
    {
        return $this->maxMinAvgSum($select, $alias, 'AVG');
    }

    /**
     * Generates a SELECT SUM(field) portion of a query
     *
     * @return $this
     */
    public function selectSum(string $select = '', string $alias = '')
    {
        return $this->maxMinAvgSum($select, $alias, 'SUM');
    }

    /**
     * Generates a SELECT COUNT(field) portion of a query
     *
     * @return $this
     */
    public function selectCount(string $select = '', string $alias = '')
    {
        return $this->maxMinAvgSum($select, $alias, 'COUNT');
    }

    /**
     * Adds a subquery to the selection
     */
    public function selectSubquery(BaseBuilder $subquery, string $as): self
    {
        $this->QBSelect[] = $this->buildSubquery($subquery, true, $as);

        return $this;
    }

    /**
     * SELECT [MAX|MIN|AVG|SUM|COUNT]()
     *
     * @used-by selectMax()
     * @used-by selectMin()
     * @used-by selectAvg()
     * @used-by selectSum()
     *
     * @return $this
     *
     * @throws DatabaseException
     * @throws DataException
     */
    protected function maxMinAvgSum(string $select = '', string $alias = '', string $type = 'MAX')
    {
        if ($select === '') {
            throw DataException::forEmptyInputGiven('Select');
        }

        if (str_contains($select, ',')) {
            throw DataException::forInvalidArgument('column name not separated by comma');
        }

        $type = strtoupper($type);

        if (! in_array($type, ['MAX', 'MIN', 'AVG', 'SUM', 'COUNT'], true)) {
            throw new DatabaseException('Invalid function type: ' . $type);
        }

        if ($alias === '') {
            $alias = $this->createAliasFromTable(trim($select));
        }

        $sql = $type . '(' . $this->db->protectIdentifiers(trim($select)) . ') AS ' . $this->db->escapeIdentifiers(trim($alias));

        $this->QBSelect[]   = $sql;
        $this->QBNoEscape[] = null;

        return $this;
    }

    /**
     * Determines the alias name based on the table
     */
    protected function createAliasFromTable(string $item): string
    {
        if (str_contains($item, '.')) {
            $item = explode('.', $item);

            return end($item);
        }

        return $item;
    }

    /**
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @return $this
     */
    public function distinct(bool $val = true)
    {
        $this->QBDistinct = $val;

        return $this;
    }

    /**
     * Generates the FROM portion of the query
     *
     * @param array|string $from
     *
     * @return $this
     */
    public function from($from, bool $overwrite = false): self
    {
        if ($overwrite) {
            $this->QBFrom = [];
            $this->db->setAliasedTables([]);
        }

        foreach ((array) $from as $table) {
            if (str_contains($table, ',')) {
                $this->from(explode(',', $table));
            } else {
                $table = trim($table);

                if ($table === '') {
                    continue;
                }

                $this->trackAliases($table);
                $this->QBFrom[] = $this->db->protectIdentifiers($table, true, null, false);
            }
        }

        return $this;
    }

    /**
     * @param BaseBuilder $from  Expected subquery
     * @param string      $alias Subquery alias
     *
     * @return $this
     */
    public function fromSubquery(BaseBuilder $from, string $alias): self
    {
        $table = $this->buildSubquery($from, true, $alias);

        $this->db->addTableAlias($alias);
        $this->QBFrom[] = $table;

        return $this;
    }

    /**
     * Generates the JOIN portion of the query
     *
     * @param RawSql|string $cond
     *
     * @return $this
     */
    public function join(string $table, $cond, string $type = '', ?bool $escape = null)
    {
        if ($type !== '') {
            $type = strtoupper(trim($type));

            if (! in_array($type, $this->joinTypes, true)) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }

        // Extract any aliases that might exist. We use this information
        // in the protectIdentifiers to know whether to add a table prefix
        $this->trackAliases($table);

        if (! is_bool($escape)) {
            $escape = $this->db->protectIdentifiers;
        }

        // Do we want to escape the table name?
        if ($escape === true) {
            $table = $this->db->protectIdentifiers($table, true, null, false);
        }

        if ($cond instanceof RawSql) {
            $this->QBJoin[] = $type . 'JOIN ' . $table . ' ON ' . $cond;

            return $this;
        }

        if (! $this->hasOperator($cond)) {
            $cond = ' USING (' . ($escape ? $this->db->escapeIdentifiers($cond) : $cond) . ')';
        } elseif ($escape === false) {
            $cond = ' ON ' . $cond;
        } else {
            // Split multiple conditions
            // @TODO This does not parse `BETWEEN a AND b` correctly.
            if (preg_match_all('/\sAND\s|\sOR\s/i', $cond, $joints, PREG_OFFSET_CAPTURE)) {
                $conditions = [];
                $joints     = $joints[0];
                array_unshift($joints, ['', 0]);

                for ($i = count($joints) - 1, $pos = strlen($cond); $i >= 0; $i--) {
                    $joints[$i][1] += strlen($joints[$i][0]); // offset
                    $conditions[$i] = substr($cond, $joints[$i][1], $pos - $joints[$i][1]);
                    $pos            = $joints[$i][1] - strlen($joints[$i][0]);
                    $joints[$i]     = $joints[$i][0];
                }
                ksort($conditions);
            } else {
                $conditions = [$cond];
                $joints     = [''];
            }

            $cond = ' ON ';

            foreach ($conditions as $i => $condition) {
                $operator = $this->getOperator($condition);

                // Workaround for BETWEEN
                if ($operator === false) {
                    $cond .= $joints[$i] . $condition;

                    continue;
                }

                $cond .= $joints[$i];
                $cond .= preg_match('/(\(*)?([\[\]\w\.\'-]+)' . preg_quote($operator, '/') . '(.*)/i', $condition, $match) ? $match[1] . $this->db->protectIdentifiers($match[2]) . $operator . $this->db->protectIdentifiers($match[3]) : $condition;
            }
        }

        // Assemble the JOIN statement
        $this->QBJoin[] = $type . 'JOIN ' . $table . $cond;

        return $this;
    }

    /**
     * Generates the WHERE portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param array|RawSql|string $key
     * @param mixed               $value
     *
     * @return $this
     */
    public function where($key, $value = null, ?bool $escape = null)
    {
        return $this->whereHaving('QBWhere', $key, $value, 'AND ', $escape);
    }

    /**
     * OR WHERE
     *
     * Generates the WHERE portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param array|RawSql|string $key
     * @param mixed               $value
     *
     * @return $this
     */
    public function orWhere($key, $value = null, ?bool $escape = null)
    {
        return $this->whereHaving('QBWhere', $key, $value, 'OR ', $escape);
    }

    /**
     * @used-by where()
     * @used-by orWhere()
     * @used-by having()
     * @used-by orHaving()
     *
     * @param array|RawSql|string $key
     * @param mixed               $value
     *
     * @return $this
     */
    protected function whereHaving(string $qbKey, $key, $value = null, string $type = 'AND ', ?bool $escape = null)
    {
        $rawSqlOnly = false;

        if ($key instanceof RawSql) {
            if ($value === null) {
                $keyValue   = [(string) $key => $key];
                $rawSqlOnly = true;
            } else {
                $keyValue = [(string) $key => $value];
            }
        } elseif (! is_array($key)) {
            $keyValue = [$key => $value];
        } else {
            $keyValue = $key;
        }

        // If the escape value was not set will base it on the global setting
        if (! is_bool($escape)) {
            $escape = $this->db->protectIdentifiers;
        }

        foreach ($keyValue as $k => $v) {
            $prefix = empty($this->{$qbKey}) ? $this->groupGetType('') : $this->groupGetType($type);

            if ($rawSqlOnly) {
                $k  = '';
                $op = '';
            } elseif ($v !== null) {
                $op = $this->getOperatorFromWhereKey($k);

                if (! empty($op)) {
                    $k = trim($k);

                    end($op);
                    $op = trim(current($op));

                    // Does the key end with operator?
                    if (str_ends_with($k, $op)) {
                        $k  = rtrim(substr($k, 0, -strlen($op)));
                        $op = " {$op}";
                    } else {
                        $op = '';
                    }
                } else {
                    $op = ' =';
                }

                if ($this->isSubquery($v)) {
                    $v = $this->buildSubquery($v, true);
                } else {
                    $bind = $this->setBind($k, $v, $escape);
                    $v    = " :{$bind}:";
                }
            } elseif (! $this->hasOperator($k) && $qbKey !== 'QBHaving') {
                // value appears not to have been set, assign the test to IS NULL
                $op = ' IS NULL';
            } elseif (
                // The key ends with !=, =, <>, IS, IS NOT
                preg_match(
                    '/\s*(!?=|<>|IS(?:\s+NOT)?)\s*$/i',
                    $k,
                    $match,
                    PREG_OFFSET_CAPTURE
                )
            ) {
                $k  = substr($k, 0, $match[0][1]);
                $op = $match[1][0] === '=' ? ' IS NULL' : ' IS NOT NULL';
            } else {
                $op = '';
            }

            if ($v instanceof RawSql) {
                $this->{$qbKey}[] = [
                    'condition' => $v->with($prefix . $k . $op . $v),
                    'escape'    => $escape,
                ];
            } else {
                $this->{$qbKey}[] = [
                    'condition' => $prefix . $k . $op . $v,
                    'escape'    => $escape,
                ];
            }
        }

        return $this;
    }

    /**
     * Generates a WHERE field IN('item', 'item') SQL query,
     * joined with 'AND' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function whereIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, false, 'AND ', $escape);
    }

    /**
     * Generates a WHERE field IN('item', 'item') SQL query,
     * joined with 'OR' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function orWhereIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, false, 'OR ', $escape);
    }

    /**
     * Generates a WHERE field NOT IN('item', 'item') SQL query,
     * joined with 'AND' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function whereNotIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, true, 'AND ', $escape);
    }

    /**
     * Generates a WHERE field NOT IN('item', 'item') SQL query,
     * joined with 'OR' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function orWhereNotIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, true, 'OR ', $escape);
    }

    /**
     * Generates a HAVING field IN('item', 'item') SQL query,
     * joined with 'AND' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function havingIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, false, 'AND ', $escape, 'QBHaving');
    }

    /**
     * Generates a HAVING field IN('item', 'item') SQL query,
     * joined with 'OR' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function orHavingIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, false, 'OR ', $escape, 'QBHaving');
    }

    /**
     * Generates a HAVING field NOT IN('item', 'item') SQL query,
     * joined with 'AND' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder):BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function havingNotIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, true, 'AND ', $escape, 'QBHaving');
    }

    /**
     * Generates a HAVING field NOT IN('item', 'item') SQL query,
     * joined with 'OR' if appropriate.
     *
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     */
    public function orHavingNotIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        return $this->_whereIn($key, $values, true, 'OR ', $escape, 'QBHaving');
    }

    /**
     * @used-by WhereIn()
     * @used-by orWhereIn()
     * @used-by whereNotIn()
     * @used-by orWhereNotIn()
     *
     * @param non-empty-string|null                                      $key
     * @param array|BaseBuilder|(Closure(BaseBuilder): BaseBuilder)|null $values The values searched on, or anonymous function with subquery
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function _whereIn(?string $key = null, $values = null, bool $not = false, string $type = 'AND ', ?bool $escape = null, string $clause = 'QBWhere')
    {
        if ($key === null || $key === '') {
            throw new InvalidArgumentException(sprintf('%s() expects $key to be a non-empty string', debug_backtrace(0, 2)[1]['function']));
        }

        if ($values === null || (! is_array($values) && ! $this->isSubquery($values))) {
            throw new InvalidArgumentException(sprintf('%s() expects $values to be of type array or closure', debug_backtrace(0, 2)[1]['function']));
        }

        if (! is_bool($escape)) {
            $escape = $this->db->protectIdentifiers;
        }

        $ok = $key;

        if ($escape === true) {
            $key = $this->db->protectIdentifiers($key);
        }

        $not = ($not) ? ' NOT' : '';

        if ($this->isSubquery($values)) {
            $whereIn = $this->buildSubquery($values, true);
            $escape  = false;
        } else {
            $whereIn = array_values($values);
        }

        $ok = $this->setBind($ok, $whereIn, $escape);

        $prefix = empty($this->{$clause}) ? $this->groupGetType('') : $this->groupGetType($type);

        $whereIn = [
            'condition' => "{$prefix}{$key}{$not} IN :{$ok}:",
            'escape'    => false,
        ];

        $this->{$clause}[] = $whereIn;

        return $this;
    }

    /**
     * Generates a %LIKE% portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function like($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'AND ', $side, '', $escape, $insensitiveSearch);
    }

    /**
     * Generates a NOT LIKE portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function notLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'AND ', $side, 'NOT', $escape, $insensitiveSearch);
    }

    /**
     * Generates a %LIKE% portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function orLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'OR ', $side, '', $escape, $insensitiveSearch);
    }

    /**
     * Generates a NOT LIKE portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function orNotLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'OR ', $side, 'NOT', $escape, $insensitiveSearch);
    }

    /**
     * Generates a %LIKE% portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function havingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'AND ', $side, '', $escape, $insensitiveSearch, 'QBHaving');
    }

    /**
     * Generates a NOT LIKE portion of the query.
     * Separates multiple calls with 'AND'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function notHavingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'AND ', $side, 'NOT', $escape, $insensitiveSearch, 'QBHaving');
    }

    /**
     * Generates a %LIKE% portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function orHavingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'OR ', $side, '', $escape, $insensitiveSearch, 'QBHaving');
    }

    /**
     * Generates a NOT LIKE portion of the query.
     * Separates multiple calls with 'OR'.
     *
     * @param array|RawSql|string $field
     *
     * @return $this
     */
    public function orNotHavingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this->_like($field, $match, 'OR ', $side, 'NOT', $escape, $insensitiveSearch, 'QBHaving');
    }

    /**
     * @used-by like()
     * @used-by orLike()
     * @used-by notLike()
     * @used-by orNotLike()
     * @used-by havingLike()
     * @used-by orHavingLike()
     * @used-by notHavingLike()
     * @used-by orNotHavingLike()
     *
     * @param array<string, string>|RawSql|string $field
     *
     * @return $this
     */
    protected function _like($field, string $match = '', string $type = 'AND ', string $side = 'both', string $not = '', ?bool $escape = null, bool $insensitiveSearch = false, string $clause = 'QBWhere')
    {
        $escape = is_bool($escape) ? $escape : $this->db->protectIdentifiers;
        $side   = strtolower($side);

        if ($field instanceof RawSql) {
            $k                 = (string) $field;
            $v                 = $match;
            $insensitiveSearch = false;

            $prefix = empty($this->{$clause}) ? $this->groupGetType('') : $this->groupGetType($type);

            if ($side === 'none') {
                $bind = $this->setBind($field->getBindingKey(), $v, $escape);
            } elseif ($side === 'before') {
                $bind = $this->setBind($field->getBindingKey(), "%{$v}", $escape);
            } elseif ($side === 'after') {
                $bind = $this->setBind($field->getBindingKey(), "{$v}%", $escape);
            } else {
                $bind = $this->setBind($field->getBindingKey(), "%{$v}%", $escape);
            }

            $likeStatement = $this->_like_statement($prefix, $k, $not, $bind, $insensitiveSearch);

            // some platforms require an escape sequence definition for LIKE wildcards
            if ($escape === true && $this->db->likeEscapeStr !== '') {
                $likeStatement .= sprintf($this->db->likeEscapeStr, $this->db->likeEscapeChar);
            }

            $this->{$clause}[] = [
                'condition' => $field->with($likeStatement),
                'escape'    => $escape,
            ];

            return $this;
        }

        $keyValue = ! is_array($field) ? [$field => $match] : $field;

        foreach ($keyValue as $k => $v) {
            if ($insensitiveSearch) {
                $v = mb_strtolower($v, 'UTF-8');
            }

            $prefix = empty($this->{$clause}) ? $this->groupGetType('') : $this->groupGetType($type);

            if ($side === 'none') {
                $bind = $this->setBind($k, $v, $escape);
            } elseif ($side === 'before') {
                $bind = $this->setBind($k, "%{$v}", $escape);
            } elseif ($side === 'after') {
                $bind = $this->setBind($k, "{$v}%", $escape);
            } else {
                $bind = $this->setBind($k, "%{$v}%", $escape);
            }

            $likeStatement = $this->_like_statement($prefix, $k, $not, $bind, $insensitiveSearch);

            // some platforms require an escape sequence definition for LIKE wildcards
            if ($escape === true && $this->db->likeEscapeStr !== '') {
                $likeStatement .= sprintf($this->db->likeEscapeStr, $this->db->likeEscapeChar);
            }

            $this->{$clause}[] = [
                'condition' => $likeStatement,
                'escape'    => $escape,
            ];
        }

        return $this;
    }

    /**
     * Platform independent LIKE statement builder.
     */
    protected function _like_statement(?string $prefix, string $column, ?string $not, string $bind, bool $insensitiveSearch = false): string
    {
        if ($insensitiveSearch) {
            return "{$prefix} LOWER(" . $this->db->escapeIdentifiers($column) . ") {$not} LIKE :{$bind}:";
        }

        return "{$prefix} {$column} {$not} LIKE :{$bind}:";
    }

    /**
     * Add UNION statement
     *
     * @param BaseBuilder|Closure(BaseBuilder): BaseBuilder $union
     *
     * @return $this
     */
    public function union($union)
    {
        return $this->addUnionStatement($union);
    }

    /**
     * Add UNION ALL statement
     *
     * @param BaseBuilder|Closure(BaseBuilder): BaseBuilder $union
     *
     * @return $this
     */
    public function unionAll($union)
    {
        return $this->addUnionStatement($union, true);
    }

    /**
     * @used-by union()
     * @used-by unionAll()
     *
     * @param BaseBuilder|Closure(BaseBuilder): BaseBuilder $union
     *
     * @return $this
     */
    protected function addUnionStatement($union, bool $all = false)
    {
        $this->QBUnion[] = "\nUNION "
            . ($all ? 'ALL ' : '')
            . 'SELECT * FROM '
            . $this->buildSubquery($union, true, 'uwrp' . (count($this->QBUnion) + 1));

        return $this;
    }

    /**
     * Starts a query group.
     *
     * @return $this
     */
    public function groupStart()
    {
        return $this->groupStartPrepare();
    }

    /**
     * Starts a query group, but ORs the group
     *
     * @return $this
     */
    public function orGroupStart()
    {
        return $this->groupStartPrepare('', 'OR ');
    }

    /**
     * Starts a query group, but NOTs the group
     *
     * @return $this
     */
    public function notGroupStart()
    {
        return $this->groupStartPrepare('NOT ');
    }

    /**
     * Starts a query group, but OR NOTs the group
     *
     * @return $this
     */
    public function orNotGroupStart()
    {
        return $this->groupStartPrepare('NOT ', 'OR ');
    }

    /**
     * Ends a query group
     *
     * @return $this
     */
    public function groupEnd()
    {
        return $this->groupEndPrepare();
    }

    /**
     * Starts a query group for HAVING clause.
     *
     * @return $this
     */
    public function havingGroupStart()
    {
        return $this->groupStartPrepare('', 'AND ', 'QBHaving');
    }

    /**
     * Starts a query group for HAVING clause, but ORs the group.
     *
     * @return $this
     */
    public function orHavingGroupStart()
    {
        return $this->groupStartPrepare('', 'OR ', 'QBHaving');
    }

    /**
     * Starts a query group for HAVING clause, but NOTs the group.
     *
     * @return $this
     */
    public function notHavingGroupStart()
    {
        return $this->groupStartPrepare('NOT ', 'AND ', 'QBHaving');
    }

    /**
     * Starts a query group for HAVING clause, but OR NOTs the group.
     *
     * @return $this
     */
    public function orNotHavingGroupStart()
    {
        return $this->groupStartPrepare('NOT ', 'OR ', 'QBHaving');
    }

    /**
     * Ends a query group for HAVING clause.
     *
     * @return $this
     */
    public function havingGroupEnd()
    {
        return $this->groupEndPrepare('QBHaving');
    }

    /**
     * Prepate a query group start.
     *
     * @return $this
     */
    protected function groupStartPrepare(string $not = '', string $type = 'AND ', string $clause = 'QBWhere')
    {
        $type = $this->groupGetType($type);

        $this->QBWhereGroupStarted = true;
        $prefix                    = empty($this->{$clause}) ? '' : $type;
        $where                     = [
            'condition' => $prefix . $not . str_repeat(' ', ++$this->QBWhereGroupCount) . ' (',
            'escape'    => false,
        ];

        $this->{$clause}[] = $where;

        return $this;
    }

    /**
     * Prepate a query group end.
     *
     * @return $this
     */
    protected function groupEndPrepare(string $clause = 'QBWhere')
    {
        $this->QBWhereGroupStarted = false;
        $where                     = [
            'condition' => str_repeat(' ', $this->QBWhereGroupCount--) . ')',
            'escape'    => false,
        ];

        $this->{$clause}[] = $where;

        return $this;
    }

    /**
     * @used-by groupStart()
     * @used-by _like()
     * @used-by whereHaving()
     * @used-by _whereIn()
     * @used-by havingGroupStart()
     */
    protected function groupGetType(string $type): string
    {
        if ($this->QBWhereGroupStarted) {
            $type                      = '';
            $this->QBWhereGroupStarted = false;
        }

        return $type;
    }

    /**
     * @param array|string $by
     *
     * @return $this
     */
    public function groupBy($by, ?bool $escape = null)
    {
        if (! is_bool($escape)) {
            $escape = $this->db->protectIdentifiers;
        }

        if (is_string($by)) {
            $by = ($escape === true) ? explode(',', $by) : [$by];
        }

        foreach ($by as $val) {
            $val = trim($val);

            if ($val !== '') {
                $val = [
                    'field'  => $val,
                    'escape' => $escape,
                ];

                $this->QBGroupBy[] = $val;
            }
        }

        return $this;
    }

    /**
     * Separates multiple calls with 'AND'.
     *
     * @param array|RawSql|string $key
     * @param mixed               $value
     *
     * @return $this
     */
    public function having($key, $value = null, ?bool $escape = null)
    {
        return $this->whereHaving('QBHaving', $key, $value, 'AND ', $escape);
    }

    /**
     * Separates multiple calls with 'OR'.
     *
     * @param array|RawSql|string $key
     * @param mixed               $value
     *
     * @return $this
     */
    public function orHaving($key, $value = null, ?bool $escape = null)
    {
        return $this->whereHaving('QBHaving', $key, $value, 'OR ', $escape);
    }

    /**
     * @param string $direction ASC, DESC or RANDOM
     *
     * @return $this
     */
    public function orderBy(string $orderBy, string $direction = '', ?bool $escape = null)
    {
        if ($orderBy === '') {
            return $this;
        }

        $qbOrderBy = [];

        $direction = strtoupper(trim($direction));

        if ($direction === 'RANDOM') {
            $direction = '';
            $orderBy   = ctype_digit($orderBy) ? sprintf($this->randomKeyword[1], $orderBy) : $this->randomKeyword[0];
            $escape    = false;
        } elseif ($direction !== '') {
            $direction = in_array($direction, ['ASC', 'DESC'], true) ? ' ' . $direction : '';
        }

        if ($escape === null) {
            $escape = $this->db->protectIdentifiers;
        }

        if ($escape === false) {
            $qbOrderBy[] = [
                'field'     => $orderBy,
                'direction' => $direction,
                'escape'    => false,
            ];
        } else {
            foreach (explode(',', $orderBy) as $field) {
                $qbOrderBy[] = ($direction === '' && preg_match('/\s+(ASC|DESC)$/i', rtrim($field), $match, PREG_OFFSET_CAPTURE))
                    ? [
                        'field'     => ltrim(substr($field, 0, $match[0][1])),
                        'direction' => ' ' . $match[1][0],
                        'escape'    => true,
                    ]
                    : [
                        'field'     => trim($field),
                        'direction' => $direction,
                        'escape'    => true,
                    ];
            }
        }

        $this->QBOrderBy = array_merge($this->QBOrderBy, $qbOrderBy);

        return $this;
    }

    /**
     * @return $this
     */
    public function limit(?int $value = null, ?int $offset = 0)
    {
        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll && $value === 0) {
            $value = null;
        }

        if ($value !== null) {
            $this->QBLimit = $value;
        }

        if ($offset !== null && $offset !== 0) {
            $this->QBOffset = $offset;
        }

        return $this;
    }

    /**
     * Sets the OFFSET value
     *
     * @return $this
     */
    public function offset(int $offset)
    {
        if ($offset !== 0) {
            $this->QBOffset = $offset;
        }

        return $this;
    }

    /**
     * Generates a platform-specific LIMIT clause.
     */
    protected function _limit(string $sql, bool $offsetIgnore = false): string
    {
        return $sql . ' LIMIT ' . ($offsetIgnore === false && $this->QBOffset ? $this->QBOffset . ', ' : '') . $this->QBLimit;
    }

    /**
     * Allows key/value pairs to be set for insert(), update() or replace().
     *
     * @param array|object|string $key    Field name, or an array of field/value pairs, or an object
     * @param mixed               $value  Field value, if $key is a single field
     * @param bool|null           $escape Whether to escape values
     *
     * @return $this
     */
    public function set($key, $value = '', ?bool $escape = null)
    {
        $key = $this->objectToArray($key);

        if (! is_array($key)) {
            $key = [$key => $value];
        }

        $escape = is_bool($escape) ? $escape : $this->db->protectIdentifiers;

        foreach ($key as $k => $v) {
            if ($escape) {
                $bind = $this->setBind($k, $v, $escape);

                $this->QBSet[$this->db->protectIdentifiers($k, false)] = ":{$bind}:";
            } else {
                $this->QBSet[$this->db->protectIdentifiers($k, false)] = $v;
            }
        }

        return $this;
    }

    /**
     * Returns the previously set() data, alternatively resetting it if needed.
     */
    public function getSetData(bool $clean = false): array
    {
        $data = $this->QBSet;

        if ($clean) {
            $this->QBSet = [];
        }

        return $data;
    }

    /**
     * Compiles a SELECT query string and returns the sql.
     */
    public function getCompiledSelect(bool $reset = true): string
    {
        $select = $this->compileSelect();

        if ($reset) {
            $this->resetSelect();
        }

        return $this->compileFinalQuery($select);
    }

    /**
     * Returns a finalized, compiled query string with the bindings
     * inserted and prefixes swapped out.
     */
    protected function compileFinalQuery(string $sql): string
    {
        $query = new Query($this->db);
        $query->setQuery($sql, $this->binds, false);

        if (! empty($this->db->swapPre) && ! empty($this->db->DBPrefix)) {
            $query->swapPrefix($this->db->DBPrefix, $this->db->swapPre);
        }

        return $query->getQuery();
    }

    /**
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @return false|ResultInterface
     */
    public function get(?int $limit = null, int $offset = 0, bool $reset = true)
    {
        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll && $limit === 0) {
            $limit = null;
        }

        if ($limit !== null) {
            $this->limit($limit, $offset);
        }

        $result = $this->testMode
            ? $this->getCompiledSelect($reset)
            : $this->db->query($this->compileSelect(), $this->binds, false);

        if ($reset) {
            $this->resetSelect();

            // Clear our binds so we don't eat up memory
            $this->binds = [];
        }

        return $result;
    }

    /**
     * Generates a platform-specific query string that counts all records in
     * the particular table
     *
     * @return int|string
     */
    public function countAll(bool $reset = true)
    {
        $table = $this->QBFrom[0];

        $sql = $this->countString . $this->db->escapeIdentifiers('numrows') . ' FROM ' .
            $this->db->protectIdentifiers($table, true, null, false);

        if ($this->testMode) {
            return $sql;
        }

        $query = $this->db->query($sql, null, false);

        if (empty($query->getResult())) {
            return 0;
        }

        $query = $query->getRow();

        if ($reset) {
            $this->resetSelect();
        }

        return (int) $query->numrows;
    }

    /**
     * Generates a platform-specific query string that counts all records
     * returned by an Query Builder query.
     *
     * @return int|string
     */
    public function countAllResults(bool $reset = true)
    {
        // ORDER BY usage is often problematic here (most notably
        // on Microsoft SQL Server) and ultimately unnecessary
        // for selecting COUNT(*) ...
        $orderBy = [];

        if (! empty($this->QBOrderBy)) {
            $orderBy = $this->QBOrderBy;

            $this->QBOrderBy = null;
        }

        // We cannot use a LIMIT when getting the single row COUNT(*) result
        $limit = $this->QBLimit;

        $this->QBLimit = false;

        if ($this->QBDistinct === true || ! empty($this->QBGroupBy)) {
            // We need to backup the original SELECT in case DBPrefix is used
            $select = $this->QBSelect;
            $sql    = $this->countString . $this->db->protectIdentifiers('numrows') . "\nFROM (\n" . $this->compileSelect() . "\n) CI_count_all_results";

            // Restore SELECT part
            $this->QBSelect = $select;
            unset($select);
        } else {
            $sql = $this->compileSelect($this->countString . $this->db->protectIdentifiers('numrows'));
        }

        if ($this->testMode) {
            return $sql;
        }

        $result = $this->db->query($sql, $this->binds, false);

        if ($reset) {
            $this->resetSelect();
        } elseif (! isset($this->QBOrderBy)) {
            $this->QBOrderBy = $orderBy;
        }

        // Restore the LIMIT setting
        $this->QBLimit = $limit;

        $row = $result instanceof ResultInterface ? $result->getRow() : null;

        if (empty($row)) {
            return 0;
        }

        return (int) $row->numrows;
    }

    /**
     * Compiles the set conditions and returns the sql statement
     *
     * @return array
     */
    public function getCompiledQBWhere()
    {
        return $this->QBWhere;
    }

    /**
     * Allows the where clause, limit and offset to be added directly
     *
     * @param array|string $where
     *
     * @return ResultInterface
     */
    public function getWhere($where = null, ?int $limit = null, ?int $offset = 0, bool $reset = true)
    {
        if ($where !== null) {
            $this->where($where);
        }

        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll && $limit === 0) {
            $limit = null;
        }

        if ($limit !== null) {
            $this->limit($limit, $offset);
        }

        $result = $this->testMode
            ? $this->getCompiledSelect($reset)
            : $this->db->query($this->compileSelect(), $this->binds, false);

        if ($reset) {
            $this->resetSelect();

            // Clear our binds so we don't eat up memory
            $this->binds = [];
        }

        return $result;
    }

    /**
     * Compiles batch insert/update/upsert strings and runs the queries
     *
     * @param '_deleteBatch'|'_insertBatch'|'_updateBatch'|'_upsertBatch' $renderMethod
     *
     * @return false|int|list<string> Number of rows inserted or FALSE on failure, SQL array when testMode
     *
     * @throws DatabaseException
     */
    protected function batchExecute(string $renderMethod, int $batchSize = 100)
    {
        if (empty($this->QBSet)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException(trim($renderMethod, '_') . '() has no data.');
            }

            return false; // @codeCoverageIgnore
        }

        $table = $this->db->protectIdentifiers($this->QBFrom[0], true, null, false);

        $affectedRows = 0;
        $savedSQL     = [];
        $cnt          = count($this->QBSet);

        // batch size 0 for unlimited
        if ($batchSize === 0) {
            $batchSize = $cnt;
        }

        for ($i = 0, $total = $cnt; $i < $total; $i += $batchSize) {
            $QBSet = array_slice($this->QBSet, $i, $batchSize);

            $sql = $this->{$renderMethod}($table, $this->QBKeys, $QBSet);

            if ($sql === '') {
                return false; // @codeCoverageIgnore
            }

            if ($this->testMode) {
                $savedSQL[] = $sql;
            } else {
                $this->db->query($sql, null, false);
                $affectedRows += $this->db->affectedRows();
            }
        }

        if (! $this->testMode) {
            $this->resetWrite();
        }

        return $this->testMode ? $savedSQL : $affectedRows;
    }

    /**
     * Allows a row or multiple rows to be set for batch inserts/upserts/updates
     *
     * @param array|object $set
     * @param string       $alias alias for sql table
     *
     * @return $this|null
     */
    public function setData($set, ?bool $escape = null, string $alias = '')
    {
        if (empty($set)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('setData() has no data.');
            }

            return null; // @codeCoverageIgnore
        }

        $this->setAlias($alias);

        // this allows to set just one row at a time
        if (is_object($set) || (! is_array(current($set)) && ! is_object(current($set)))) {
            $set = [$set];
        }

        $set = $this->batchObjectToArray($set);

        $escape = is_bool($escape) ? $escape : $this->db->protectIdentifiers;

        $keys = array_keys($this->objectToArray(current($set)));
        sort($keys);

        foreach ($set as $row) {
            $row = $this->objectToArray($row);
            if (array_diff($keys, array_keys($row)) !== [] || array_diff(array_keys($row), $keys) !== []) {
                // batchExecute() function returns an error on an empty array
                $this->QBSet[] = [];

                return null;
            }

            ksort($row); // puts $row in the same order as our keys

            $clean = [];

            foreach ($row as $rowValue) {
                $clean[] = $escape ? $this->db->escape($rowValue) : $rowValue;
            }

            $row = $clean;

            $this->QBSet[] = $row;
        }

        foreach ($keys as $k) {
            $k = $this->db->protectIdentifiers($k, false);

            if (! in_array($k, $this->QBKeys, true)) {
                $this->QBKeys[] = $k;
            }
        }

        return $this;
    }

    /**
     * Compiles an upsert query and returns the sql
     *
     * @return string
     *
     * @throws DatabaseException
     */
    public function getCompiledUpsert()
    {
        [$currentTestMode, $this->testMode] = [$this->testMode, true];

        $sql = implode(";\n", $this->upsert());

        $this->testMode = $currentTestMode;

        return $this->compileFinalQuery($sql);
    }

    /**
     * Converts call to batchUpsert
     *
     * @param array|object|null $set
     *
     * @return false|int|list<string> Number of affected rows or FALSE on failure, SQL array when testMode
     *
     * @throws DatabaseException
     */
    public function upsert($set = null, ?bool $escape = null)
    {
        // if set() has been used merge QBSet with binds and then setData()
        if ($set === null && ! is_array(current($this->QBSet))) {
            $set = [];

            foreach ($this->QBSet as $field => $value) {
                $k = trim($field, $this->db->escapeChar);
                // use binds if available else use QBSet value but with RawSql to avoid escape
                $set[$k] = isset($this->binds[$k]) ? $this->binds[$k][0] : new RawSql($value);
            }

            $this->binds = [];

            $this->resetRun([
                'QBSet'  => [],
                'QBKeys' => [],
            ]);

            $this->setData($set, true); // unescaped items are RawSql now
        } elseif ($set !== null) {
            $this->setData($set, $escape);
        } // else setData() has already been used and we need to do nothing

        return $this->batchExecute('_upsertBatch');
    }

    /**
     * Compiles batch upsert strings and runs the queries
     *
     * @param array|object|null $set a dataset
     *
     * @return false|int|list<string> Number of affected rows or FALSE on failure, SQL array when testMode
     *
     * @throws DatabaseException
     */
    public function upsertBatch($set = null, ?bool $escape = null, int $batchSize = 100)
    {
        if (isset($this->QBOptions['setQueryAsData'])) {
            $sql = $this->_upsertBatch($this->QBFrom[0], $this->QBKeys, []);

            if ($sql === '') {
                return false; // @codeCoverageIgnore
            }

            if ($this->testMode === false) {
                $this->db->query($sql, null, false);
            }

            $this->resetWrite();

            return $this->testMode ? $sql : $this->db->affectedRows();
        }

        if ($set !== null) {
            $this->setData($set, $escape);
        }

        return $this->batchExecute('_upsertBatch', $batchSize);
    }

    /**
     * Generates a platform-specific upsertBatch string from the supplied data
     *
     * @used-by batchExecute()
     *
     * @param string                 $table  Protected table name
     * @param list<string>           $keys   QBKeys
     * @param list<list<int|string>> $values QBSet
     */
    protected function _upsertBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $updateFields = $this->QBOptions['updateFields'] ?? $this->updateFields($keys)->QBOptions['updateFields'] ?? [];

            $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $keys) . ")\n{:_table_:}ON DUPLICATE KEY UPDATE\n" . implode(
                ",\n",
                array_map(
                    static fn ($key, $value) => $table . '.' . $key . ($value instanceof RawSql ?
                        ' = ' . $value :
                        ' = VALUES(' . $value . ')'),
                    array_keys($updateFields),
                    $updateFields
                )
            );

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'] . "\n";
        } else {
            $data = 'VALUES ' . implode(', ', $this->formatValues($values)) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Set table alias for dataset pseudo table.
     */
    private function setAlias(string $alias): BaseBuilder
    {
        if ($alias !== '') {
            $this->db->addTableAlias($alias);
            $this->QBOptions['alias'] = $this->db->protectIdentifiers($alias);
        }

        return $this;
    }

    /**
     * Sets update fields for upsert, update
     *
     * @param list<RawSql>|list<string>|string $set
     * @param bool                             $addToDefault adds update fields to the default ones
     * @param array|null                       $ignore       ignores items in set
     *
     * @return $this
     */
    public function updateFields($set, bool $addToDefault = false, ?array $ignore = null)
    {
        if (! empty($set)) {
            if (! is_array($set)) {
                $set = explode(',', $set);
            }

            foreach ($set as $key => $value) {
                if (! ($value instanceof RawSql)) {
                    $value = $this->db->protectIdentifiers($value);
                }

                if (is_numeric($key)) {
                    $key = $value;
                }

                if ($ignore === null || ! in_array($key, $ignore, true)) {
                    if ($addToDefault) {
                        $this->QBOptions['updateFieldsAdditional'][$this->db->protectIdentifiers($key)] = $value;
                    } else {
                        $this->QBOptions['updateFields'][$this->db->protectIdentifiers($key)] = $value;
                    }
                }
            }

            if ($addToDefault === false && isset($this->QBOptions['updateFieldsAdditional'], $this->QBOptions['updateFields'])) {
                $this->QBOptions['updateFields'] = array_merge($this->QBOptions['updateFields'], $this->QBOptions['updateFieldsAdditional']);

                unset($this->QBOptions['updateFieldsAdditional']);
            }
        }

        return $this;
    }

    /**
     * Sets constraints for batch upsert, update
     *
     * @param array|RawSql|string $set a string of columns, key value pairs, or RawSql
     *
     * @return $this
     */
    public function onConstraint($set)
    {
        if (! empty($set)) {
            if (is_string($set)) {
                $set = explode(',', $set);

                $set = array_map(static fn ($key) => trim($key), $set);
            }

            if ($set instanceof RawSql) {
                $set = [$set];
            }

            foreach ($set as $key => $value) {
                if (! ($value instanceof RawSql)) {
                    $value = $this->db->protectIdentifiers($value);
                }

                if (is_string($key)) {
                    $key = $this->db->protectIdentifiers($key);
                }

                $this->QBOptions['constraints'][$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Sets data source as a query for insertBatch()/updateBatch()/upsertBatch()/deleteBatch()
     *
     * @param BaseBuilder|RawSql $query
     * @param array|string|null  $columns an array or comma delimited string of columns
     */
    public function setQueryAsData($query, ?string $alias = null, $columns = null): BaseBuilder
    {
        if (is_string($query)) {
            throw new InvalidArgumentException('$query parameter must be BaseBuilder or RawSql class.');
        }

        if ($query instanceof BaseBuilder) {
            $query = $query->getCompiledSelect();
        } elseif ($query instanceof RawSql) {
            $query = $query->__toString();
        }

        if (is_string($query)) {
            if ($columns !== null && is_string($columns)) {
                $columns = explode(',', $columns);
                $columns = array_map(static fn ($key) => trim($key), $columns);
            }

            $columns = (array) $columns;

            if ($columns === []) {
                $columns = $this->fieldsFromQuery($query);
            }

            if ($alias !== null) {
                $this->setAlias($alias);
            }

            foreach ($columns as $key => $value) {
                $columns[$key] = $this->db->escapeChar . $value . $this->db->escapeChar;
            }

            $this->QBOptions['setQueryAsData'] = $query;
            $this->QBKeys                      = $columns;
            $this->QBSet                       = [];
        }

        return $this;
    }

    /**
     * Gets column names from a select query
     */
    protected function fieldsFromQuery(string $sql): array
    {
        return $this->db->query('SELECT * FROM (' . $sql . ') _u_ LIMIT 1')->getFieldNames();
    }

    /**
     * Converts value array of array to array of strings
     */
    protected function formatValues(array $values): array
    {
        return array_map(static fn ($index) => '(' . implode(',', $index) . ')', $values);
    }

    /**
     * Compiles batch insert strings and runs the queries
     *
     * @param array|object|null $set a dataset
     *
     * @return false|int|list<string> Number of rows inserted or FALSE on failure, SQL array when testMode
     */
    public function insertBatch($set = null, ?bool $escape = null, int $batchSize = 100)
    {
        if (isset($this->QBOptions['setQueryAsData'])) {
            $sql = $this->_insertBatch($this->QBFrom[0], $this->QBKeys, []);

            if ($sql === '') {
                return false; // @codeCoverageIgnore
            }

            if ($this->testMode === false) {
                $this->db->query($sql, null, false);
            }

            $this->resetWrite();

            return $this->testMode ? $sql : $this->db->affectedRows();
        }

        if ($set !== null && $set !== []) {
            $this->setData($set, $escape);
        }

        return $this->batchExecute('_insertBatch', $batchSize);
    }

    /**
     * Generates a platform-specific insert string from the supplied data.
     *
     * @used-by batchExecute()
     *
     * @param string                 $table  Protected table name
     * @param list<string>           $keys   QBKeys
     * @param list<list<int|string>> $values QBSet
     */
    protected function _insertBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $sql = 'INSERT ' . $this->compileIgnore('insert') . 'INTO ' . $table
                . ' (' . implode(', ', $keys) . ")\n{:_table_:}";

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = 'VALUES ' . implode(', ', $this->formatValues($values));
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Allows key/value pairs to be set for batch inserts
     *
     * @param mixed $key
     *
     * @return $this|null
     *
     * @deprecated
     */
    public function setInsertBatch($key, string $value = '', ?bool $escape = null)
    {
        if (! is_array($key)) {
            $key = [[$key => $value]];
        }

        return $this->setData($key, $escape);
    }

    /**
     * Compiles an insert query and returns the sql
     *
     * @return bool|string
     *
     * @throws DatabaseException
     */
    public function getCompiledInsert(bool $reset = true)
    {
        if ($this->validateInsert() === false) {
            return false;
        }

        $sql = $this->_insert(
            $this->db->protectIdentifiers(
                $this->removeAlias($this->QBFrom[0]),
                true,
                null,
                false
            ),
            array_keys($this->QBSet),
            array_values($this->QBSet)
        );

        if ($reset) {
            $this->resetWrite();
        }

        return $this->compileFinalQuery($sql);
    }

    /**
     * Compiles an insert string and runs the query
     *
     * @param array|object|null $set
     *
     * @return BaseResult|bool|Query
     *
     * @throws DatabaseException
     */
    public function insert($set = null, ?bool $escape = null)
    {
        if ($set !== null) {
            $this->set($set, '', $escape);
        }

        if ($this->validateInsert() === false) {
            return false;
        }

        $sql = $this->_insert(
            $this->db->protectIdentifiers(
                $this->removeAlias($this->QBFrom[0]),
                true,
                $escape,
                false
            ),
            array_keys($this->QBSet),
            array_values($this->QBSet)
        );

        if (! $this->testMode) {
            $this->resetWrite();

            $result = $this->db->query($sql, $this->binds, false);

            // Clear our binds so we don't eat up memory
            $this->binds = [];

            return $result;
        }

        return false;
    }

    /**
     * @internal This is a temporary solution.
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/5376
     *
     * @TODO Fix a root cause, and this method should be removed.
     */
    protected function removeAlias(string $from): string
    {
        if (str_contains($from, ' ')) {
            // if the alias is written with the AS keyword, remove it
            $from = preg_replace('/\s+AS\s+/i', ' ', $from);

            $parts = explode(' ', $from);
            $from  = $parts[0];
        }

        return $from;
    }

    /**
     * This method is used by both insert() and getCompiledInsert() to
     * validate that the there data is actually being set and that table
     * has been chosen to be inserted into.
     *
     * @throws DatabaseException
     */
    protected function validateInsert(): bool
    {
        if (empty($this->QBSet)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('You must use the "set" method to insert an entry.');
            }

            return false; // @codeCoverageIgnore
        }

        return true;
    }

    /**
     * Generates a platform-specific insert string from the supplied data
     *
     * @param string           $table         Protected table name
     * @param list<string>     $keys          Keys of QBSet
     * @param list<int|string> $unescapedKeys Values of QBSet
     */
    protected function _insert(string $table, array $keys, array $unescapedKeys): string
    {
        return 'INSERT ' . $this->compileIgnore('insert') . 'INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $unescapedKeys) . ')';
    }

    /**
     * Compiles a replace into string and runs the query
     *
     * @return BaseResult|false|Query|string
     *
     * @throws DatabaseException
     */
    public function replace(?array $set = null)
    {
        if ($set !== null) {
            $this->set($set);
        }

        if (empty($this->QBSet)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('You must use the "set" method to update an entry.');
            }

            return false; // @codeCoverageIgnore
        }

        $table = $this->QBFrom[0];

        $sql = $this->_replace($table, array_keys($this->QBSet), array_values($this->QBSet));

        $this->resetWrite();

        return $this->testMode ? $sql : $this->db->query($sql, $this->binds, false);
    }

    /**
     * Generates a platform-specific replace string from the supplied data
     *
     * @param string           $table  Protected table name
     * @param list<string>     $keys   Keys of QBSet
     * @param list<int|string> $values Values of QBSet
     */
    protected function _replace(string $table, array $keys, array $values): string
    {
        return 'REPLACE INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ')';
    }

    /**
     * Groups tables in FROM clauses if needed, so there is no confusion
     * about operator precedence.
     *
     * Note: This is only used (and overridden) by MySQL and SQLSRV.
     */
    protected function _fromTables(): string
    {
        return implode(', ', $this->QBFrom);
    }

    /**
     * Compiles an update query and returns the sql
     *
     * @return bool|string
     */
    public function getCompiledUpdate(bool $reset = true)
    {
        if ($this->validateUpdate() === false) {
            return false;
        }

        $sql = $this->_update($this->QBFrom[0], $this->QBSet);

        if ($reset) {
            $this->resetWrite();
        }

        return $this->compileFinalQuery($sql);
    }

    /**
     * Compiles an update string and runs the query.
     *
     * @param array|object|null        $set
     * @param array|RawSql|string|null $where
     *
     * @throws DatabaseException
     */
    public function update($set = null, $where = null, ?int $limit = null): bool
    {
        if ($set !== null) {
            $this->set($set);
        }

        if ($this->validateUpdate() === false) {
            return false;
        }

        if ($where !== null) {
            $this->where($where);
        }

        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll && $limit === 0) {
            $limit = null;
        }

        if ($limit !== null) {
            if (! $this->canLimitWhereUpdates) {
                throw new DatabaseException('This driver does not allow LIMITs on UPDATE queries using WHERE.');
            }

            $this->limit($limit);
        }

        $sql = $this->_update($this->QBFrom[0], $this->QBSet);

        if (! $this->testMode) {
            $this->resetWrite();

            $result = $this->db->query($sql, $this->binds, false);

            if ($result !== false) {
                // Clear our binds so we don't eat up memory
                $this->binds = [];

                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Generates a platform-specific update string from the supplied data
     *
     * @param string                $table  Protected table name
     * @param array<string, string> $values QBSet
     */
    protected function _update(string $table, array $values): string
    {
        $valStr = [];

        foreach ($values as $key => $val) {
            $valStr[] = $key . ' = ' . $val;
        }

        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll) {
            return 'UPDATE ' . $this->compileIgnore('update') . $table . ' SET ' . implode(', ', $valStr)
                . $this->compileWhereHaving('QBWhere')
                . $this->compileOrderBy()
                . ($this->QBLimit ? $this->_limit(' ', true) : '');
        }

        return 'UPDATE ' . $this->compileIgnore('update') . $table . ' SET ' . implode(', ', $valStr)
            . $this->compileWhereHaving('QBWhere')
            . $this->compileOrderBy()
            . ($this->QBLimit !== false ? $this->_limit(' ', true) : '');
    }

    /**
     * This method is used by both update() and getCompiledUpdate() to
     * validate that data is actually being set and that a table has been
     * chosen to be updated.
     *
     * @throws DatabaseException
     */
    protected function validateUpdate(): bool
    {
        if (empty($this->QBSet)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('You must use the "set" method to update an entry.');
            }

            return false; // @codeCoverageIgnore
        }

        return true;
    }

    /**
     * Sets data and calls batchExecute to run queries
     *
     * @param array|object|null        $set         a dataset
     * @param array|RawSql|string|null $constraints
     *
     * @return false|int|list<string> Number of rows affected or FALSE on failure, SQL array when testMode
     */
    public function updateBatch($set = null, $constraints = null, int $batchSize = 100)
    {
        $this->onConstraint($constraints);

        if (isset($this->QBOptions['setQueryAsData'])) {
            $sql = $this->_updateBatch($this->QBFrom[0], $this->QBKeys, []);

            if ($sql === '') {
                return false; // @codeCoverageIgnore
            }

            if ($this->testMode === false) {
                $this->db->query($sql, null, false);
            }

            $this->resetWrite();

            return $this->testMode ? $sql : $this->db->affectedRows();
        }

        if ($set !== null && $set !== []) {
            $this->setData($set, true);
        }

        return $this->batchExecute('_updateBatch', $batchSize);
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     *
     * @used-by batchExecute()
     *
     * @param string                 $table  Protected table name
     * @param list<string>           $keys   QBKeys
     * @param list<list<int|string>> $values QBSet
     */
    protected function _updateBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if ($constraints === []) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('You must specify a constraint to match on for batch updates.'); // @codeCoverageIgnore
                }

                return ''; // @codeCoverageIgnore
            }

            $updateFields = $this->QBOptions['updateFields'] ??
                $this->updateFields($keys, false, $constraints)->QBOptions['updateFields'] ??
                [];

            $alias = $this->QBOptions['alias'] ?? '_u';

            $sql = 'UPDATE ' . $this->compileIgnore('update') . $table . "\n";

            $sql .= "SET\n";

            $sql .= implode(
                ",\n",
                array_map(
                    static fn ($key, $value) => $key . ($value instanceof RawSql ?
                        ' = ' . $value :
                        ' = ' . $alias . '.' . $value),
                    array_keys($updateFields),
                    $updateFields
                )
            ) . "\n";

            $sql .= "FROM (\n{:_table_:}";

            $sql .= ') ' . $alias . "\n";

            $sql .= 'WHERE ' . implode(
                ' AND ',
                array_map(
                    static fn ($key, $value) => (
                        ($value instanceof RawSql && is_string($key))
                        ?
                        $table . '.' . $key . ' = ' . $value
                        :
                        (
                            $value instanceof RawSql
                            ?
                            $value
                            :
                            $table . '.' . $value . ' = ' . $alias . '.' . $value
                        )
                    ),
                    array_keys($constraints),
                    $constraints
                )
            );

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = implode(
                " UNION ALL\n",
                array_map(
                    static fn ($value) => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index) => $index . ' ' . $key,
                        $keys,
                        $value
                    )),
                    $values
                )
            ) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Allows key/value pairs to be set for batch updating
     *
     * @param array|object $key
     *
     * @return $this
     *
     * @throws DatabaseException
     *
     * @deprecated
     */
    public function setUpdateBatch($key, string $index = '', ?bool $escape = null)
    {
        if ($index !== '') {
            $this->onConstraint($index);
        }

        $this->setData($key, $escape);

        return $this;
    }

    /**
     * Compiles a delete string and runs "DELETE FROM table"
     *
     * @return bool|string TRUE on success, FALSE on failure, string on testMode
     */
    public function emptyTable()
    {
        $table = $this->QBFrom[0];

        $sql = $this->_delete($table);

        if ($this->testMode) {
            return $sql;
        }

        $this->resetWrite();

        return $this->db->query($sql, null, false);
    }

    /**
     * Compiles a truncate string and runs the query
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @return bool|string TRUE on success, FALSE on failure, string on testMode
     */
    public function truncate()
    {
        $table = $this->QBFrom[0];

        $sql = $this->_truncate($table);

        if ($this->testMode) {
            return $sql;
        }

        $this->resetWrite();

        return $this->db->query($sql, null, false);
    }

    /**
     * Generates a platform-specific truncate string from the supplied data
     *
     * If the database does not support the truncate() command,
     * then this method maps to 'DELETE FROM table'
     *
     * @param string $table Protected table name
     */
    protected function _truncate(string $table): string
    {
        return 'TRUNCATE ' . $table;
    }

    /**
     * Compiles a delete query string and returns the sql
     */
    public function getCompiledDelete(bool $reset = true): string
    {
        $sql = $this->testMode()->delete('', null, $reset);
        $this->testMode(false);

        return $this->compileFinalQuery($sql);
    }

    /**
     * Compiles a delete string and runs the query
     *
     * @param array|RawSql|string $where
     *
     * @return bool|string Returns a SQL string if in test mode.
     *
     * @throws DatabaseException
     */
    public function delete($where = '', ?int $limit = null, bool $resetData = true)
    {
        $table = $this->db->protectIdentifiers($this->QBFrom[0], true, null, false);

        if ($where !== '') {
            $this->where($where);
        }

        if (empty($this->QBWhere)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Deletes are not allowed unless they contain a "where" or "like" clause.');
            }

            return false; // @codeCoverageIgnore
        }

        $sql = $this->_delete($this->removeAlias($table));

        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll && $limit === 0) {
            $limit = null;
        }

        if ($limit !== null) {
            $this->QBLimit = $limit;
        }

        if (! empty($this->QBLimit)) {
            if (! $this->canLimitDeletes) {
                throw new DatabaseException('SQLite3 does not allow LIMITs on DELETE queries.');
            }

            $sql = $this->_limit($sql, true);
        }

        if ($resetData) {
            $this->resetWrite();
        }

        return $this->testMode ? $sql : $this->db->query($sql, $this->binds, false);
    }

    /**
     * Sets data and calls batchExecute to run queries
     *
     * @param array|object|null $set         a dataset
     * @param array|RawSql|null $constraints
     *
     * @return false|int|list<string> Number of rows affected or FALSE on failure, SQL array when testMode
     */
    public function deleteBatch($set = null, $constraints = null, int $batchSize = 100)
    {
        $this->onConstraint($constraints);

        if (isset($this->QBOptions['setQueryAsData'])) {
            $sql = $this->_deleteBatch($this->QBFrom[0], $this->QBKeys, []);

            if ($sql === '') {
                return false; // @codeCoverageIgnore
            }

            if ($this->testMode === false) {
                $this->db->query($sql, null, false);
            }

            $this->resetWrite();

            return $this->testMode ? $sql : $this->db->affectedRows();
        }

        if ($set !== null && $set !== []) {
            $this->setData($set, true);
        }

        return $this->batchExecute('_deleteBatch', $batchSize);
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     *
     * @used-by batchExecute()
     *
     * @param string           $table  Protected table name
     * @param list<string>     $keys   QBKeys
     * @param list<int|string> $values QBSet
     */
    protected function _deleteBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if ($constraints === []) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('You must specify a constraint to match on for batch deletes.'); // @codeCoverageIgnore
                }

                return ''; // @codeCoverageIgnore
            }

            $alias = $this->QBOptions['alias'] ?? '_u';

            $sql = 'DELETE ' . $table . ' FROM ' . $table . "\n";

            $sql .= "INNER JOIN (\n{:_table_:}";

            $sql .= ') ' . $alias . "\n";

            $sql .= 'ON ' . implode(
                ' AND ',
                array_map(
                    static fn ($key, $value) => (
                        $value instanceof RawSql ?
                        $value :
                        (
                            is_string($key) ?
                            $table . '.' . $key . ' = ' . $alias . '.' . $value :
                            $table . '.' . $value . ' = ' . $alias . '.' . $value
                        )
                    ),
                    array_keys($constraints),
                    $constraints
                )
            );

            // convert binds in where
            foreach ($this->QBWhere as $key => $where) {
                foreach ($this->binds as $field => $bind) {
                    $this->QBWhere[$key]['condition'] = str_replace(':' . $field . ':', $bind[0], $where['condition']);
                }
            }

            $sql .= ' ' . $this->compileWhereHaving('QBWhere');

            $this->QBOptions['sql'] = trim($sql);
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = implode(
                " UNION ALL\n",
                array_map(
                    static fn ($value) => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index) => $index . ' ' . $key,
                        $keys,
                        $value
                    )),
                    $values
                )
            ) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Increments a numeric column by the specified value.
     *
     * @return bool
     */
    public function increment(string $column, int $value = 1)
    {
        $column = $this->db->protectIdentifiers($column);

        $sql = $this->_update($this->QBFrom[0], [$column => "{$column} + {$value}"]);

        if (! $this->testMode) {
            $this->resetWrite();

            return $this->db->query($sql, $this->binds, false);
        }

        return true;
    }

    /**
     * Decrements a numeric column by the specified value.
     *
     * @return bool
     */
    public function decrement(string $column, int $value = 1)
    {
        $column = $this->db->protectIdentifiers($column);

        $sql = $this->_update($this->QBFrom[0], [$column => "{$column}-{$value}"]);

        if (! $this->testMode) {
            $this->resetWrite();

            return $this->db->query($sql, $this->binds, false);
        }

        return true;
    }

    /**
     * Generates a platform-specific delete string from the supplied data
     *
     * @param string $table Protected table name
     */
    protected function _delete(string $table): string
    {
        return 'DELETE ' . $this->compileIgnore('delete') . 'FROM ' . $table . $this->compileWhereHaving('QBWhere');
    }

    /**
     * Used to track SQL statements written with aliased tables.
     *
     * @param array|string $table The table to inspect
     *
     * @return string|void
     */
    protected function trackAliases($table)
    {
        if (is_array($table)) {
            foreach ($table as $t) {
                $this->trackAliases($t);
            }

            return;
        }

        // Does the string contain a comma?  If so, we need to separate
        // the string into discreet statements
        if (str_contains($table, ',')) {
            return $this->trackAliases(explode(',', $table));
        }

        // if a table alias is used we can recognize it by a space
        if (str_contains($table, ' ')) {
            // if the alias is written with the AS keyword, remove it
            $table = preg_replace('/\s+AS\s+/i', ' ', $table);

            // Grab the alias
            $table = trim(strrchr($table, ' '));

            // Store the alias, if it doesn't already exist
            $this->db->addTableAlias($table);
        }
    }

    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     * Should not be called directly.
     *
     * @param mixed $selectOverride
     */
    protected function compileSelect($selectOverride = false): string
    {
        if ($selectOverride !== false) {
            $sql = $selectOverride;
        } else {
            $sql = (! $this->QBDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

            if (empty($this->QBSelect)) {
                $sql .= '*';
            } else {
                // Cycle through the "select" portion of the query and prep each column name.
                // The reason we protect identifiers here rather than in the select() function
                // is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->QBSelect as $key => $val) {
                    if ($val instanceof RawSql) {
                        $this->QBSelect[$key] = (string) $val;
                    } else {
                        $protect              = $this->QBNoEscape[$key] ?? null;
                        $this->QBSelect[$key] = $this->db->protectIdentifiers($val, false, $protect);
                    }
                }

                $sql .= implode(', ', $this->QBSelect);
            }
        }

        if (! empty($this->QBFrom)) {
            $sql .= "\nFROM " . $this->_fromTables();
        }

        if (! empty($this->QBJoin)) {
            $sql .= "\n" . implode("\n", $this->QBJoin);
        }

        $sql .= $this->compileWhereHaving('QBWhere')
            . $this->compileGroupBy()
            . $this->compileWhereHaving('QBHaving')
            . $this->compileOrderBy();

        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll) {
            if ($this->QBLimit) {
                $sql = $this->_limit($sql . "\n");
            }
        } elseif ($this->QBLimit !== false || $this->QBOffset) {
            $sql = $this->_limit($sql . "\n");
        }

        return $this->unionInjection($sql);
    }

    /**
     * Checks if the ignore option is supported by
     * the Database Driver for the specific statement.
     *
     * @return string
     */
    protected function compileIgnore(string $statement)
    {
        if ($this->QBIgnore && isset($this->supportedIgnoreStatements[$statement])) {
            return trim($this->supportedIgnoreStatements[$statement]) . ' ';
        }

        return '';
    }

    /**
     * Escapes identifiers in WHERE and HAVING statements at execution time.
     *
     * Required so that aliases are tracked properly, regardless of whether
     * where(), orWhere(), having(), orHaving are called prior to from(),
     * join() and prefixTable is added only if needed.
     *
     * @param string $qbKey 'QBWhere' or 'QBHaving'
     *
     * @return string SQL statement
     */
    protected function compileWhereHaving(string $qbKey): string
    {
        if (! empty($this->{$qbKey})) {
            foreach ($this->{$qbKey} as &$qbkey) {
                // Is this condition already compiled?
                if (is_string($qbkey)) {
                    continue;
                }

                if ($qbkey instanceof RawSql) {
                    continue;
                }

                if ($qbkey['condition'] instanceof RawSql) {
                    $qbkey = $qbkey['condition'];

                    continue;
                }

                if ($qbkey['escape'] === false) {
                    $qbkey = $qbkey['condition'];

                    continue;
                }

                // Split multiple conditions
                $conditions = preg_split(
                    '/((?:^|\s+)AND\s+|(?:^|\s+)OR\s+)/i',
                    $qbkey['condition'],
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                );

                foreach ($conditions as &$condition) {
                    $op = $this->getOperator($condition);
                    if (
                        $op === false
                        || preg_match(
                            '/^(\(?)(.*)(' . preg_quote($op, '/') . ')\s*(.*(?<!\)))?(\)?)$/i',
                            $condition,
                            $matches
                        ) !== 1
                    ) {
                        continue;
                    }
                    // $matches = [
                    //  0 => '(test <= foo)',   /* the whole thing */
                    //  1 => '(',               /* optional */
                    //  2 => 'test',            /* the field name */
                    //  3 => ' <= ',            /* $op */
                    //  4 => 'foo',	            /* optional, if $op is e.g. 'IS NULL' */
                    //  5 => ')'                /* optional */
                    // ];

                    if (isset($matches[4]) && $matches[4] !== '') {
                        $protectIdentifiers = false;
                        if (str_contains($matches[4], '.')) {
                            $protectIdentifiers = true;
                        }

                        if (! str_contains($matches[4], ':')) {
                            $matches[4] = $this->db->protectIdentifiers(trim($matches[4]), false, $protectIdentifiers);
                        }

                        $matches[4] = ' ' . $matches[4];
                    }

                    $condition = $matches[1] . $this->db->protectIdentifiers(trim($matches[2]))
                        . ' ' . trim($matches[3]) . $matches[4] . $matches[5];
                }

                $qbkey = implode('', $conditions);
            }

            return ($qbKey === 'QBHaving' ? "\nHAVING " : "\nWHERE ")
                . implode("\n", $this->{$qbKey});
        }

        return '';
    }

    /**
     * Escapes identifiers in GROUP BY statements at execution time.
     *
     * Required so that aliases are tracked properly, regardless of whether
     * groupBy() is called prior to from(), join() and prefixTable is added
     * only if needed.
     */
    protected function compileGroupBy(): string
    {
        if (! empty($this->QBGroupBy)) {
            foreach ($this->QBGroupBy as &$groupBy) {
                // Is it already compiled?
                if (is_string($groupBy)) {
                    continue;
                }

                $groupBy = ($groupBy['escape'] === false || $this->isLiteral($groupBy['field']))
                    ? $groupBy['field']
                    : $this->db->protectIdentifiers($groupBy['field']);
            }

            return "\nGROUP BY " . implode(', ', $this->QBGroupBy);
        }

        return '';
    }

    /**
     * Escapes identifiers in ORDER BY statements at execution time.
     *
     * Required so that aliases are tracked properly, regardless of whether
     * orderBy() is called prior to from(), join() and prefixTable is added
     * only if needed.
     */
    protected function compileOrderBy(): string
    {
        if (is_array($this->QBOrderBy) && $this->QBOrderBy !== []) {
            foreach ($this->QBOrderBy as &$orderBy) {
                if ($orderBy['escape'] !== false && ! $this->isLiteral($orderBy['field'])) {
                    $orderBy['field'] = $this->db->protectIdentifiers($orderBy['field']);
                }

                $orderBy = $orderBy['field'] . $orderBy['direction'];
            }

            return $this->QBOrderBy = "\nORDER BY " . implode(', ', $this->QBOrderBy);
        }

        if (is_string($this->QBOrderBy)) {
            return $this->QBOrderBy;
        }

        return '';
    }

    protected function unionInjection(string $sql): string
    {
        if ($this->QBUnion === []) {
            return $sql;
        }

        return 'SELECT * FROM (' . $sql . ') '
            . ($this->db->protectIdentifiers ? $this->db->escapeIdentifiers('uwrp0') : 'uwrp0')
            . implode("\n", $this->QBUnion);
    }

    /**
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param array|object $object
     *
     * @return array
     */
    protected function objectToArray($object)
    {
        if (! is_object($object)) {
            return $object;
        }

        if ($object instanceof RawSql) {
            throw new InvalidArgumentException('RawSql "' . $object . '" cannot be used here.');
        }

        $array = [];

        foreach (get_object_vars($object) as $key => $val) {
            if ((! is_object($val) || $val instanceof RawSql) && ! is_array($val)) {
                $array[$key] = $val;
            }
        }

        return $array;
    }

    /**
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param array|object $object
     *
     * @return array
     */
    protected function batchObjectToArray($object)
    {
        if (! is_object($object)) {
            return $object;
        }

        $array  = [];
        $out    = get_object_vars($object);
        $fields = array_keys($out);

        foreach ($fields as $val) {
            $i = 0;

            foreach ($out[$val] as $data) {
                $array[$i++][$val] = $data;
            }
        }

        return $array;
    }

    /**
     * Determines if a string represents a literal value or a field name
     */
    protected function isLiteral(string $str): bool
    {
        $str = trim($str);

        if ($str === ''
            || ctype_digit($str)
            || (string) (float) $str === $str
            || in_array(strtoupper($str), ['TRUE', 'FALSE'], true)
        ) {
            return true;
        }

        if ($this->isLiteralStr === []) {
            $this->isLiteralStr = $this->db->escapeChar !== '"' ? ['"', "'"] : ["'"];
        }

        return in_array($str[0], $this->isLiteralStr, true);
    }

    /**
     * Publicly-visible method to reset the QB values.
     *
     * @return $this
     */
    public function resetQuery()
    {
        $this->resetSelect();
        $this->resetWrite();

        return $this;
    }

    /**
     * Resets the query builder values.  Called by the get() function
     *
     * @param array $qbResetItems An array of fields to reset
     */
    protected function resetRun(array $qbResetItems)
    {
        foreach ($qbResetItems as $item => $defaultValue) {
            $this->{$item} = $defaultValue;
        }
    }

    /**
     * Resets the query builder values.  Called by the get() function
     */
    protected function resetSelect()
    {
        $this->resetRun([
            'QBSelect'   => [],
            'QBJoin'     => [],
            'QBWhere'    => [],
            'QBGroupBy'  => [],
            'QBHaving'   => [],
            'QBOrderBy'  => [],
            'QBNoEscape' => [],
            'QBDistinct' => false,
            'QBLimit'    => false,
            'QBOffset'   => false,
            'QBUnion'    => [],
        ]);

        if (! empty($this->db)) {
            $this->db->setAliasedTables([]);
        }

        // Reset QBFrom part
        if (! empty($this->QBFrom)) {
            $this->from(array_shift($this->QBFrom), true);
        }
    }

    /**
     * Resets the query builder "write" values.
     *
     * Called by the insert() update() insertBatch() updateBatch() and delete() functions
     */
    protected function resetWrite()
    {
        $this->resetRun([
            'QBSet'     => [],
            'QBJoin'    => [],
            'QBWhere'   => [],
            'QBOrderBy' => [],
            'QBKeys'    => [],
            'QBLimit'   => false,
            'QBIgnore'  => false,
            'QBOptions' => [],
        ]);
    }

    /**
     * Tests whether the string has an SQL operator
     */
    protected function hasOperator(string $str): bool
    {
        return preg_match(
            '/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i',
            trim($str)
        ) === 1;
    }

    /**
     * Returns the SQL string operator
     *
     * @return array|false|string
     */
    protected function getOperator(string $str, bool $list = false)
    {
        if ($this->pregOperators === []) {
            $_les = $this->db->likeEscapeStr !== ''
                ? '\s+' . preg_quote(trim(sprintf($this->db->likeEscapeStr, $this->db->likeEscapeChar)), '/')
                : '';
            $this->pregOperators = [
                '\s*(?:<|>|!)?=\s*', // =, <=, >=, !=
                '\s*<>?\s*',         // <, <>
                '\s*>\s*',           // >
                '\s+IS NULL',             // IS NULL
                '\s+IS NOT NULL',         // IS NOT NULL
                '\s+EXISTS\s*\(.*\)',     // EXISTS (sql)
                '\s+NOT EXISTS\s*\(.*\)', // NOT EXISTS(sql)
                '\s+BETWEEN\s+',          // BETWEEN value AND value
                '\s+IN\s*\(.*\)',         // IN (list)
                '\s+NOT IN\s*\(.*\)',     // NOT IN (list)
                '\s+LIKE\s+\S.*(' . $_les . ')?',     // LIKE 'expr'[ ESCAPE '%s']
                '\s+NOT LIKE\s+\S.*(' . $_les . ')?', // NOT LIKE 'expr'[ ESCAPE '%s']
            ];
        }

        return preg_match_all(
            '/' . implode('|', $this->pregOperators) . '/i',
            $str,
            $match
        ) ? ($list ? $match[0] : $match[0][0]) : false;
    }

    /**
     * Returns the SQL string operator from where key
     *
     * @return false|list<string>
     */
    private function getOperatorFromWhereKey(string $whereKey)
    {
        $whereKey = trim($whereKey);

        $pregOperators = [
            '\s*(?:<|>|!)?=',         // =, <=, >=, !=
            '\s*<>?',                 // <, <>
            '\s*>',                   // >
            '\s+IS NULL',             // IS NULL
            '\s+IS NOT NULL',         // IS NOT NULL
            '\s+EXISTS\s*\(.*\)',     // EXISTS (sql)
            '\s+NOT EXISTS\s*\(.*\)', // NOT EXISTS (sql)
            '\s+BETWEEN\s+',          // BETWEEN value AND value
            '\s+IN\s*\(.*\)',         // IN (list)
            '\s+NOT IN\s*\(.*\)',     // NOT IN (list)
            '\s+LIKE',                // LIKE
            '\s+NOT LIKE',            // NOT LIKE
        ];

        return preg_match_all(
            '/' . implode('|', $pregOperators) . '/i',
            $whereKey,
            $match
        ) ? $match[0] : false;
    }

    /**
     * Stores a bind value after ensuring that it's unique.
     * While it might be nicer to have named keys for our binds array
     * with PHP 7+ we get a huge memory/performance gain with indexed
     * arrays instead, so lets take advantage of that here.
     *
     * @param mixed $value
     */
    protected function setBind(string $key, $value = null, bool $escape = true): string
    {
        if (! array_key_exists($key, $this->binds)) {
            $this->binds[$key] = [
                $value,
                $escape,
            ];

            return $key;
        }

        if (! array_key_exists($key, $this->bindsKeyCount)) {
            $this->bindsKeyCount[$key] = 1;
        }

        $count = $this->bindsKeyCount[$key]++;

        $this->binds[$key . '.' . $count] = [
            $value,
            $escape,
        ];

        return $key . '.' . $count;
    }

    /**
     * Returns a clone of a Base Builder with reset query builder values.
     *
     * @return $this
     *
     * @deprecated
     */
    protected function cleanClone()
    {
        return (clone $this)->from([], true)->resetQuery();
    }

    /**
     * @param mixed $value
     */
    protected function isSubquery($value): bool
    {
        return $value instanceof BaseBuilder || $value instanceof Closure;
    }

    /**
     * @param BaseBuilder|Closure(BaseBuilder): BaseBuilder $builder
     * @param bool                                          $wrapped Wrap the subquery in brackets
     * @param string                                        $alias   Subquery alias
     */
    protected function buildSubquery($builder, bool $wrapped = false, string $alias = ''): string
    {
        if ($builder instanceof Closure) {
            $builder($builder = $this->db->newQuery());
        }

        if ($builder === $this) {
            throw new DatabaseException('The subquery cannot be the same object as the main query object.');
        }

        $subquery = strtr($builder->getCompiledSelect(false), "\n", ' ');

        if ($wrapped) {
            $subquery = '(' . $subquery . ')';
            $alias    = trim($alias);

            if ($alias !== '') {
                $subquery .= ' ' . ($this->db->protectIdentifiers ? $this->db->escapeIdentifiers($alias) : $alias);
            }
        }

        return $subquery;
    }
}
