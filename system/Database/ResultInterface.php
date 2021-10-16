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
 * Interface ResultInterface
 */
interface ResultInterface
{
    /**
     * Retrieve the results of the query. Typically an array of
     * individual data rows, which can be either an 'array', an
     * 'object', or a custom class name.
     *
     * @param string $type The row type. Either 'array', 'object', or a class name to use
     */
    public function getResult(string $type = 'object'): array;

    /**
     * Returns the results as an array of custom objects.
     *
     * @param string $className The name of the class to use.
     *
     * @return mixed
     */
    public function getCustomResultObject(string $className);

    /**
     * Returns the results as an array of arrays.
     *
     * If no results, an empty array is returned.
     */
    public function getResultArray(): array;

    /**
     * Returns the results as an array of objects.
     *
     * If no results, an empty array is returned.
     */
    public function getResultObject(): array;

    /**
     * Wrapper object to return a row as either an array, an object, or
     * a custom class.
     *
     * If row doesn't exist, returns null.
     *
     * @param mixed  $n    The index of the results to return
     * @param string $type The type of result object. 'array', 'object' or class name.
     *
     * @return mixed
     */
    public function getRow($n = 0, string $type = 'object');

    /**
     * Returns a row as a custom class instance.
     *
     * If row doesn't exists, returns null.
     *
     * @return mixed
     */
    public function getCustomRowObject(int $n, string $className);

    /**
     * Returns a single row from the results as an array.
     *
     * If row doesn't exist, returns null.
     *
     * @return mixed
     */
    public function getRowArray(int $n = 0);

    /**
     * Returns a single row from the results as an object.
     *
     * If row doesn't exist, returns null.
     *
     * @return mixed
     */
    public function getRowObject(int $n = 0);

    /**
     * Assigns an item into a particular column slot.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setRow($key, $value = null);

    /**
     * Returns the "first" row of the current results.
     *
     * @return mixed
     */
    public function getFirstRow(string $type = 'object');

    /**
     * Returns the "last" row of the current results.
     *
     * @return mixed
     */
    public function getLastRow(string $type = 'object');

    /**
     * Returns the "next" row of the current results.
     *
     * @return mixed
     */
    public function getNextRow(string $type = 'object');

    /**
     * Returns the "previous" row of the current results.
     *
     * @return mixed
     */
    public function getPreviousRow(string $type = 'object');

    /**
     * Returns an unbuffered row and move the pointer to the next row.
     *
     * @return mixed
     */
    public function getUnbufferedRow(string $type = 'object');

    /**
     * Gets the number of fields in the result set.
     */
    public function getFieldCount(): int;

    /**
     * Generates an array of column names in the result set.
     */
    public function getFieldNames(): array;

    /**
     * Generates an array of objects representing field meta-data.
     */
    public function getFieldData(): array;

    /**
     * Frees the current result.
     */
    public function freeResult();

    /**
     * Moves the internal pointer to the desired offset. This is called
     * internally before fetching results to make sure the result set
     * starts at zero.
     *
     * @return mixed
     */
    public function dataSeek(int $n = 0);
}
