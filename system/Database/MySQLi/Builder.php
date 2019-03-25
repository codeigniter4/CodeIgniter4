<?php namespace CodeIgniter\Database\MySQLi;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

use CodeIgniter\Database\BaseBuilder;

/**
 * Builder for MySQLi
 */
class Builder extends BaseBuilder
{

	/**
	 * Identifier escape character
	 *
	 * @var string
	 */
	protected $escapeChar = '`';

    //--------------------------------------------------------------------

    /**
     * Insert batch statement
     *
     * Generates a platform-specific insert string from the supplied data.
     *
     * @param string $table  Table name
     * @param array  $keys   INSERT keys
     * @param array  $values INSERT values
     *
     * @return string
     */
    protected function _insertBatch($table, $keys, $values)
    {
        return 'INSERT ' . ($this->insertIgnore ? 'IGNORE ' : '') . 'INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES ' . implode(', ', $values);
    }

    //--------------------------------------------------------------------

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param string $table         The table name
     * @param array  $keys          The insert keys
     * @param array  $unescapedKeys The insert values
     *
     * @return string
     */
    protected function _insert($table, array $keys, array $unescapedKeys)
    {
        return 'INSERT ' . ($this->insertIgnore ? 'IGNORE ' : '') . 'INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $unescapedKeys) . ')';
    }

}
