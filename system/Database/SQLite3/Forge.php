<?php

namespace CodeIgniter\Database\SQLite3;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Forge for SQLite3
 */
class Forge extends \CodeIgniter\Database\Forge {
    // --------------------------------------------------------------------

    /**
     * Create database
     *
     * @param	string	$db_name
     * @return	bool
     */
    public function createDatabase($db_name) {
        // In SQLite, a database is created when you connect to the database.
        // We'll return TRUE so that an error isn't generated
        return TRUE;
    }

    /**
     * Drop database
     *
     * @param	string	$db_name
     * @return	bool
     */
    public function dropDatabase($db_name) {
        // In SQLite, a database is created when you connect to the database.
        // We'll return TRUE so that an error isn't generated
        return TRUE;
    }

    /**
     * Field attribute AUTO_INCREMENT
     *
     * @param    array &$attributes
     * @param    array &$field
     *
     * @return    void
     */
    protected function _attributeAutoIncrement(&$attributes, &$field) {
        if (!empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true &&
                stripos($field['type'], 'int') !== false
        ) {
            $field['auto_increment'] = ' PRIMARY KEY';
        }
    }
}
