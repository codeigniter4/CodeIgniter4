<?php
/**
 * Sprint
 *
 * A set of power tools to enhance the CodeIgniter framework and provide consistent workflow.
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
 * @package     Sprint
 * @author      Lonnie Ezell
 * @copyright   Copyright 2014-2015, New Myth Media, LLC (http://newmythmedia.com)
 * @license     http://opensource.org/licenses/MIT  (MIT)
 * @link        http://sprintphp.com
 * @since       Version 1.0
 */

$descriptions = [
	'migration' => ['migration <name>', 'Creates a new migration file.']
];

$long_description = <<<EOT
NAME
	migration - creates a new migration file.

SYNOPSIS
	migration <name> [options]

DESCRIPTION
	Will create a new migration file using the migration library settings and the <name> passed in to determine the name of the file.
	The system scans <name> for common words to help describe the action the migration should take, like creating a table,
	adding a column, or dropping a table or column.

	The migration name will must have words separated by underscores (_) as per the CodeIgniter requirements. This also allows the
	generator to attempt to determine the correct action that should be taken, like creating a table, or adding/dropping a column.
	When a table name is specified it must be followed by '_table'. When a column name is specified, it must be followed by '_column'.

	Example migration names:
		create_user_table               // Creates a new table called 'user'
		make_role_table                 // Creates a new table called 'role'
		add_name_column_to_log_table    // Adds a new column called 'name' to the 'log' table
		insert_age_column_log_table     // Adds a new column called 'age' to the 'log' table
		remove_age_column_from_log_table // Removes the 'age' column from the 'log' table
		drop_age_column_log_table       // Removes the 'age' column from the 'log' table
		delete_age_column_log_table     // Removes the 'age' column from the 'log' table

	Fields must adhere to the following rule when being passed in via the -fields option:
		- Each field is described with column_name:field_type
		- A third segment can be present that determines the field length, joined with a colon.
		- If the 'type' segment is 'id', then a INT(9) UNSIGNED primary key is created.

	Examples of Fields:
		name:string             // A VARCHAR(255) called 'name'
		age:int:3               // An INT(3) called 'age'
		"name:string age:int:3" // Must be in quotes when called with multiple fields
		uuid:id                 // Creates a primary key called 'uuid'

OPTIONS
	-fields     A quoted string with the names and types of fields to use when creating a table.

	-fromdb     If present, will override -fields values and attempt to pull the values from an existing database table. This has no value, the table is discovered from the migration name.


EOT;
