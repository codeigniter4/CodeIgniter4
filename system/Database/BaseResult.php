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

use CodeIgniter\Entity\Entity;
use stdClass;

/**
 * @template TConnection
 * @template TResult
 *
 * @implements ResultInterface<TConnection, TResult>
 */
abstract class BaseResult implements ResultInterface
{
    /**
     * @var TConnection
     */
    public $connID;

    /**
     * @var false|TResult
     */
    public $resultID;

    /**
     * @var list<array>
     */
    public $resultArray = [];

    /**
     * @var list<object>
     */
    public $resultObject = [];

    /**
     * @var array<class-string, list<object>>
     */
    public $customResultObject = [];

    /**
     * Current row index
     *
     * @var int
     */
    public $currentRow = 0;

    /**
     * The number of records in the query result
     *
     * @var int|null
     */
    protected $numRows;

    /**
     * @var array<string, mixed>|null
     */
    public $rowData;

    /**
     * @param TConnection $connID
     * @param TResult     $resultID
     */
    public function __construct(&$connID, &$resultID)
    {
        $this->connID   = $connID;
        $this->resultID = $resultID;
    }

    public function getResult(string $type = 'object'): array
    {
        if ($type === 'array') {
            return $this->getResultArray();
        }

        if ($type === 'object') {
            return $this->getResultObject();
        }

        return $this->getCustomResultObject($type);
    }

    public function getCustomResultObject(string $className)
    {
        if (isset($this->customResultObject[$className])) {
            return $this->customResultObject[$className];
        }

        if (! $this->isValidResultId()) {
            return [];
        }

        // Don't fetch the result set again if we already have it
        $_data = null;
        if (($c = count($this->resultArray)) > 0) {
            $_data = 'resultArray';
        } elseif (($c = count($this->resultObject)) > 0) {
            $_data = 'resultObject';
        }

        if ($_data !== null) {
            for ($i = 0; $i < $c; $i++) {
                $this->customResultObject[$className][$i] = new $className();

                foreach ($this->{$_data}[$i] as $key => $value) {
                    $this->customResultObject[$className][$i]->{$key} = $value;
                }
            }

            return $this->customResultObject[$className];
        }

        if ($this->rowData !== null) {
            $this->dataSeek();
        }
        $this->customResultObject[$className] = [];

        while ($row = $this->fetchObject($className)) {
            if (! is_subclass_of($row, Entity::class) && method_exists($row, 'syncOriginal')) {
                $row->syncOriginal();
            }

            $this->customResultObject[$className][] = $row;
        }

        return $this->customResultObject[$className];
    }

    public function getResultArray(): array
    {
        if ($this->resultArray !== []) {
            return $this->resultArray;
        }

        // In the event that query caching is on, the result_id variable
        // will not be a valid resource so we'll simply return an empty
        // array.
        if (! $this->isValidResultId()) {
            return [];
        }

        if ($this->resultObject !== []) {
            foreach ($this->resultObject as $row) {
                $this->resultArray[] = (array) $row;
            }

            return $this->resultArray;
        }

        if ($this->rowData !== null) {
            $this->dataSeek();
        }

        while ($row = $this->fetchAssoc()) {
            $this->resultArray[] = $row;
        }

        return $this->resultArray;
    }

    public function getResultObject(): array
    {
        if ($this->resultObject !== []) {
            return $this->resultObject;
        }

        // In the event that query caching is on, the result_id variable
        // will not be a valid resource so we'll simply return an empty
        // array.
        if (! $this->isValidResultId()) {
            return [];
        }

        if ($this->resultArray !== []) {
            foreach ($this->resultArray as $row) {
                $this->resultObject[] = (object) $row;
            }

            return $this->resultObject;
        }

        if ($this->rowData !== null) {
            $this->dataSeek();
        }

        while ($row = $this->fetchObject()) {
            if (! is_subclass_of($row, Entity::class) && method_exists($row, 'syncOriginal')) {
                $row->syncOriginal();
            }

            $this->resultObject[] = $row;
        }

        return $this->resultObject;
    }

    public function getRow($n = 0, string $type = 'object')
    {
        // $n is a column name.
        if (! is_numeric($n)) {
            // We cache the row data for subsequent uses
            if (! is_array($this->rowData)) {
                $this->rowData = $this->getRowArray();
            }

            // array_key_exists() instead of isset() to allow for NULL values
            if ($this->rowData === null || ! array_key_exists($n, $this->rowData)) {
                return null;
            }

            return $this->rowData[$n];
        }

        if ($type === 'object') {
            return $this->getRowObject($n);
        }

        if ($type === 'array') {
            return $this->getRowArray($n);
        }

        return $this->getCustomRowObject($n, $type);
    }

    public function getCustomRowObject(int $n, string $className)
    {
        if (! isset($this->customResultObject[$className])) {
            $this->getCustomResultObject($className);
        }

        if (($this->customResultObject[$className] ?? []) === []) {
            return null;
        }

        if ($n !== $this->currentRow && isset($this->customResultObject[$className][$n])) {
            $this->currentRow = $n;
        }

        return $this->customResultObject[$className][$this->currentRow] ?? null;
    }

    public function getRowArray(int $n = 0)
    {
        $result = $this->getResultArray();
        if ($result === []) {
            return null;
        }

        if ($n !== $this->currentRow && isset($result[$n])) {
            $this->currentRow = $n;
        }

        return $result[$this->currentRow] ?? null;
    }

    public function getRowObject(int $n = 0)
    {
        $result = $this->getResultObject();
        if ($result === []) {
            return null;
        }

        if ($n !== $this->currentRow && isset($result[$n])) {
            $this->currentRow = $n;
        }

        return $result[$this->currentRow] ?? null;
    }

    public function setRow($key, $value = null)
    {
        // We cache the row data for subsequent uses
        if (! is_array($this->rowData)) {
            $this->rowData = $this->getRowArray();
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->rowData[$k] = $v;
            }

            return;
        }

        if ($key !== '' && $value !== null) {
            $this->rowData[$key] = $value;
        }
    }

    public function getFirstRow(string $type = 'object')
    {
        $result = $this->getResult($type);

        return ($result === []) ? null : $result[0];
    }

    public function getLastRow(string $type = 'object')
    {
        $result = $this->getResult($type);

        return ($result === []) ? null : $result[count($result) - 1];
    }

    public function getNextRow(string $type = 'object')
    {
        $result = $this->getResult($type);
        if ($result === []) {
            return null;
        }

        return isset($result[$this->currentRow + 1]) ? $result[++$this->currentRow] : null;
    }

    public function getPreviousRow(string $type = 'object')
    {
        $result = $this->getResult($type);
        if ($result === []) {
            return null;
        }

        if (isset($result[$this->currentRow - 1])) {
            $this->currentRow--;
        }

        return $result[$this->currentRow] ?? null;
    }

    public function getUnbufferedRow(string $type = 'object')
    {
        if ($type === 'array') {
            return $this->fetchAssoc();
        }

        if ($type === 'object') {
            return $this->fetchObject();
        }

        return $this->fetchObject($type);
    }

    /**
     * Number of rows in the result set; checks for previous count, falls
     * back on counting resultArray or resultObject, finally fetching resultArray
     * if nothing was previously fetched
     */
    public function getNumRows(): int
    {
        if (is_int($this->numRows)) {
            return $this->numRows;
        }
        if ($this->resultArray !== []) {
            return $this->numRows = count($this->resultArray);
        }
        if ($this->resultObject !== []) {
            return $this->numRows = count($this->resultObject);
        }

        return $this->numRows = count($this->getResultArray());
    }

    private function isValidResultId(): bool
    {
        return is_resource($this->resultID) || is_object($this->resultID);
    }

    /**
     * Returns the result set as an array.
     *
     * Overridden by driver classes.
     *
     * @return array<string, mixed>|false|null
     */
    abstract protected function fetchAssoc();

    /**
     * Returns the result set as an object.
     *
     * @param class-string $className
     *
     * @return false|object
     */
    abstract protected function fetchObject(string $className = stdClass::class);
}
