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
    'model' => ['model <name>', 'Creates a new model file that extends from CIDbModel.']
];

$long_description = <<<EOT
NAME
	model - creates a new model.

SYNOPSIS
	model <name> [options]

DESCRIPTION
	Provides a skeleton model file that extends Myth\Models\CIDbModel.

	When called without a model name, it will ask for the table name and the most common options from you.

	When called with a model name in the CLI it will assume typical defaults:

		- pluralising the model name (less 'model') for the table name,
		- 'id' for the primary key
		- will track created_on and modified_on dates
		- 'datetime' format
		- will NOT use soft deletes
		- will NOT log user activity.

	No matter how you call it, if a table exists with that name in the database already, it will analyse the table
	and create very basic validation rules for you.

	You will want to customize to match your project's needs.

OPTIONS
	-table          The name of the database table to use

	-primary_key    The name of the column to use as the primary key

	-set_created    If 'y', informs the model to automatically set created_on timestamps

	-set_modified   If 'y', informs the model to automatically set modified_on timestamps

	-date_format    Format to store created_on and modified_on values. Either 'date', 'datetime' or 'int'

	-soft_delete    If 'y', informs the model to use soft deletes instead of permenant deletes.

	-log_user       If 'y', informs the model to track who created, modified or deleted objects.
EOT;
