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

namespace CodeIgniter\Database\SQLSRV;

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\BadMethodCallException;

/**
 * Prepared query for SQLSRV
 *
 * @extends BasePreparedQuery<resource, resource, resource, Connection>
 */
class PreparedQuery extends BasePreparedQuery
{
    /**
     * Parameters array used to store the dynamic variables.
     *
     * @var array<int, mixed>
     */
    protected $parameters = [];

    public function __construct(Connection $db)
    {
        parent::__construct($db);
    }

    /**
     * @param array<array-key, mixed> $options Positional array of bind values, keyed by placeholder index.
     *
     * @throws DatabaseException
     */
    public function _prepare(string $sql, array $options = []): PreparedQuery
    {
        // Prepare parameters for the query
        $queryString = $this->getQueryString();

        $parameters = $this->parameterize($queryString, $options);

        // Prepare the query
        $this->statement = sqlsrv_prepare($this->db->connID, $sql, $parameters);

        if (! $this->statement) {
            $info                    = $this->db->error();
            $this->databaseException = $this->db->createDatabaseException($this->db->getAllErrorMessages(), $info['code']);

            if ($this->db->DBDebug) {
                throw $this->databaseException;
            }

            $this->errorCode   = is_int($info['code']) ? $info['code'] : 0;
            $this->errorString = $info['message'];
        }

        return $this;
    }

    public function _execute(array $data): bool
    {
        if (! isset($this->statement)) {
            throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
        }

        foreach ($data as $key => $value) {
            $this->parameters[$key] = $value;
        }

        $result = sqlsrv_execute($this->statement);

        if ($result === false) {
            $error = $this->db->error();

            $this->errorCode         = is_int($error['code']) ? $error['code'] : 0;
            $this->errorString       = $this->db->getAllErrorMessages();
            $this->databaseException = $this->db->createDatabaseException($this->errorString, $error['code']);

            if ($this->db->DBDebug) {
                throw $this->databaseException;
            }
        }

        return $result;
    }

    /**
     * @return resource|null
     */
    public function _getResult()
    {
        return $this->statement;
    }

    protected function _close(): bool
    {
        return sqlsrv_free_stmt($this->statement);
    }

    /**
     * Handle parameters.
     *
     * @param array<array-key, mixed> $options
     *
     * @return list<mixed>
     */
    protected function parameterize(string $queryString, array $options): array
    {
        $numberOfVariables = substr_count($queryString, '?');

        $params = [];

        for ($c = 0; $c < $numberOfVariables; $c++) {
            $this->parameters[$c] = null;
            if (isset($options[$c])) {
                $params[] = [&$this->parameters[$c], SQLSRV_PARAM_IN, $options[$c]];
            } else {
                $params[] = &$this->parameters[$c];
            }
        }

        return $params;
    }
}
