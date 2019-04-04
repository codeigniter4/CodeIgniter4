# Running System Tests

This is the quick-start to CodeIgniter testing. Its intent is to describe what 
it takes to get your system setup and ready to run the system tests. 
It is not intended to be a full description of the test features that you can 
use to test your application, since that can be found in the documentation. 

## Requirements

It is recommended to use the latest version of PHPUnit. At the time of this 
writing we are running version 7.5.1. Support for this has been built into the 
**composer.json** file that ships with CodeIgniter, and can easily be installed 
via [Composer](https://getcomposer.org/) if you don't already have it installed globally.

	> composer install

If running under OS X or Linux, you can create a symbolic link to make running tests a touch nicer.

	> ln -s ./vendor/bin/phpunit ./phpunit

You also need to install [XDebug](https://xdebug.org/index.php) in order
for the unit tests to successfully complete.

## Setup

A number of the tests that are ran during the test suite are ran against a running database. 
In order to setup the database used here, edit the details for the `tests` database 
group in **app/Config/Database.php**. Make sure that you provide a database engine 
that is currently running, and have already created a table that you can use only 
for these tests, as it will be wiped and refreshed often while running the test suite.  

A simplified PHPunit configuration file, **phpunit.dist.xml**, is provided.
You can use it as is, or copy it to **phpunit.xml**, and tailor it to suit your
application.

## Running the tests

The entire test suite can be ran by simply typing one command from the command line within the main directory.

	> ./phpunit

You can limit tests to those within a single test directory by specifying the 
directory name after phpunit. 

	> ./phpunit app/Models

## Generating Code Coverage

To generate coverage information, including HTML reports you can view in your browser, 
you can use the following command: 

	> ./phpunit --colors --coverage-text=tests/coverage.txt --coverage-html=tests/coverage/ -d memory_limit=1024m

This runs all of the tests again, collecting information about how many lines, 
functions, and files are tested, and the percent of the code that is covered by the tests. 
It is collected in two formats: a simple text file that provides an overview, 
as well as comprehensive collection of HTML files that show the status of every line of code in the project. 

Code coverage details will be left in the **tests/coverage** folder.
