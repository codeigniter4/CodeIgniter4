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
	'scaffold' => ['scaffold <name>', 'Creates an MVC triad based around a single data type.']
];

$long_description = <<<EOT
NAME
	scaffold - creates the models, views, controllers and migrations necessary for a single data type.

SYNOPSIS
	scaffold <name> [options]

DESCRIPTION
	Given the name of a single data type, like 'post', it creates the Model, Controller with basic CRUD operations,
	the required views and the migration necessary to quickly scaffold out, or prototype, a new data type. This is
	intended to quickly create code that can be edited as needed. Regenerating the code will overwrite the files,
	not update them.

	All HTML will be generating using the current UIKit as specified in `application/config/application.php`.

OPTIONS
	-fields     A quoted string with the names and types of fields to use when creating a table.

	-fromdb     If present, will override -fields values and attempt to pull the values from an existing database table. This has no value, the table is discovered from the migration name.

	-module     If present, will create this as a separate module, instead of incorporating it into the existing app.
EOT;
