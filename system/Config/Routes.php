<?php
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

/**
 * System URI Routing
 *
 * This file contains any routing to system tools, such as command-line
 * tools for migrations, etc.
 *
 * It is called by Config\Routes, and has the $routes RouteCollection
 * already loaded up and ready for us to use.
 */
// Prevent access to BaseController
$routes->add('basecontroller(:any)', function()
{
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
}); 

// Migrations
$routes->cli('migrations/(:segment)/(:segment)', '\CodeIgniter\Commands\MigrationsCommand::$1/$2');
$routes->cli('migrations/(:segment)', '\CodeIgniter\Commands\MigrationsCommand::$1');
$routes->cli('migrations', '\CodeIgniter\Commands\MigrationsCommand::index');

// CLI Catchall - uses a _remap to call Commands
$routes->cli('ci(:any)', '\CodeIgniter\CLI\CommandRunner::index/$1');

// Prevent access to initController method
$routes->add('(:any)/initController', function()
{
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
}); 
