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

use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Class BaseUtils
 */
abstract class BaseUtils
{
    /**
     * Database object
     *
     * @var object
     */
    protected $db;

    /**
     * List databases statement
     *
     * @var bool|string
     */
    protected $listDatabases = false;

    /**
     * OPTIMIZE TABLE statement
     *
     * @var bool|string
     */
    protected $optimizeTable = false;

    /**
     * REPAIR TABLE statement
     *
     * @var bool|string
     */
    protected $repairTable = false;

    /**
     * Class constructor
     */
    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    /**
     * List databases
     *
     * @throws DatabaseException
     *
     * @return array|bool
     */
    public function listDatabases()
    {
        // Is there a cached result?
        if (isset($this->db->dataCache['db_names'])) {
            return $this->db->dataCache['db_names'];
        }

        if ($this->listDatabases === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Unsupported feature of the database platform you are using.');
            }

            return false;
        }

        $this->db->dataCache['db_names'] = [];

        $query = $this->db->query($this->listDatabases);
        if ($query === false) {
            return $this->db->dataCache['db_names'];
        }

        for ($i = 0, $query = $query->getResultArray(), $c = count($query); $i < $c; $i++) {
            $this->db->dataCache['db_names'][] = current($query[$i]);
        }

        return $this->db->dataCache['db_names'];
    }

    /**
     * Determine if a particular database exists
     */
    public function databaseExists(string $databaseName): bool
    {
        return in_array($databaseName, $this->listDatabases(), true);
    }

    /**
     * Optimize Table
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function optimizeTable(string $tableName)
    {
        if ($this->optimizeTable === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Unsupported feature of the database platform you are using.');
            }

            return false;
        }

        $query = $this->db->query(sprintf($this->optimizeTable, $this->db->escapeIdentifiers($tableName)));

        return $query !== false;
    }

    /**
     * Optimize Database
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function optimizeDatabase()
    {
        if ($this->optimizeTable === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Unsupported feature of the database platform you are using.');
            }

            return false;
        }

        $result = [];

        foreach ($this->db->listTables() as $tableName) {
            $res = $this->db->query(sprintf($this->optimizeTable, $this->db->escapeIdentifiers($tableName)));
            if (is_bool($res)) {
                return $res;
            }

            // Build the result array...

            $res = $res->getResultArray();

            // Postgre & SQLite3 returns empty array
            if (empty($res)) {
                $key = $tableName;
            } else {
                $res  = current($res);
                $key  = str_replace($this->db->database . '.', '', current($res));
                $keys = array_keys($res);
                unset($res[$keys[0]]);
            }

            $result[$key] = $res;
        }

        return $result;
    }

    /**
     * Repair Table
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function repairTable(string $tableName)
    {
        if ($this->repairTable === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Unsupported feature of the database platform you are using.');
            }

            return false;
        }

        $query = $this->db->query(sprintf($this->repairTable, $this->db->escapeIdentifiers($tableName)));
        if (is_bool($query)) {
            return $query;
        }

        $query = $query->getResultArray();

        return current($query);
    }

    /**
     * Generate CSV from a query result object
     *
     * @return string
     */
    public function getCSVFromResult(ResultInterface $query, string $delim = ',', string $newline = "\n", string $enclosure = '"')
    {
        $out = '';

        foreach ($query->getFieldNames() as $name) {
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $name) . $enclosure . $delim;
        }

        $out = substr($out, 0, -strlen($delim)) . $newline;

        // Next blast through the result array and build out the rows
        while ($row = $query->getUnbufferedRow('array')) {
            $line = [];

            foreach ($row as $item) {
                $line[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $item ?? '') . $enclosure;
            }

            $out .= implode($delim, $line) . $newline;
        }

        return $out;
    }

    /**
     * Generate XML data from a query result object
     */
    public function getXMLFromResult(ResultInterface $query, array $params = []): string
    {
        foreach (['root' => 'root', 'element' => 'element', 'newline' => "\n", 'tab' => "\t"] as $key => $val) {
            if (! isset($params[$key])) {
                $params[$key] = $val;
            }
        }

        $root    = $params['root'];
        $newline = $params['newline'];
        $tab     = $params['tab'];
        $element = $params['element'];

        helper('xml');
        $xml = '<' . $root . '>' . $newline;

        while ($row = $query->getUnbufferedRow()) {
            $xml .= $tab . '<' . $element . '>' . $newline;

            foreach ($row as $key => $val) {
                $val = (! empty($val)) ? xml_convert($val) : '';

                $xml .= $tab . $tab . '<' . $key . '>' . $val . '</' . $key . '>' . $newline;
            }

            $xml .= $tab . '</' . $element . '>' . $newline;
        }

        return $xml . '</' . $root . '>' . $newline;
    }

    /**
     * Database Backup
     *
     * @param array|string $params
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function backup($params = [])
    {
        if (is_string($params)) {
            $params = ['tables' => $params];
        }

        $prefs = [
            'tables'             => [],
            'ignore'             => [],
            'filename'           => '',
            'format'             => 'gzip', // gzip, txt
            'add_drop'           => true,
            'add_insert'         => true,
            'newline'            => "\n",
            'foreign_key_checks' => true,
        ];

        if (! empty($params)) {
            foreach (array_keys($prefs) as $key) {
                if (isset($params[$key])) {
                    $prefs[$key] = $params[$key];
                }
            }
        }

        if (empty($prefs['tables'])) {
            $prefs['tables'] = $this->db->listTables();
        }

        if (! in_array($prefs['format'], ['gzip', 'txt'], true)) {
            $prefs['format'] = 'txt';
        }

        if ($prefs['format'] === 'gzip' && ! function_exists('gzencode')) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('The file compression format you chose is not supported by your server.');
            }

            $prefs['format'] = 'txt';
        }

        if ($prefs['format'] === 'txt') {
            return $this->_backup($prefs);
        }

        return gzencode($this->_backup($prefs));
    }

    /**
     * Platform dependent version of the backup function.
     *
     * @return mixed
     */
    abstract public function _backup(?array $prefs = null);
}
