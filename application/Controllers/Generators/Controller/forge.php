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
    'controller' => ['controller <name> [<base>]', 'Creates a new controller file that extends from <base> or BaseController.']
];

$long_description = <<<EOT
NAME
	controller - creates a new controller and possibly it's CRUD.

SYNOPSIS
	controller <name> [options]

DESCRIPTION
	At its most basic, creates a new Controller with stub outs for the common CRUD methods.

	If the -themed option is present it will do two things. First, the controller will extend from
	Myth\Controllers\ThemedController instead of Myth\Controllers\BaseController. Second, it will
	create the basic code to generate a working set of CRUD methods, as well as their views, ready
	for you to customize.

	If the -model option is present, it will add the model to be autoloaded. Since a model is present
	it will also force the use of themes and generate all of the CRUD code for you.

OPTIONS
	-model  The name of a model to autoload. If present, also acts as if -themed option is passed.

	-themed If present, forces use of a ThemedController and generates views.
EOT;
