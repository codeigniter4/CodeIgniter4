# Running System Tests

This is the quick-start to CodeIgniter testing. Its intent is to describe what 
it takes to set up your system and get it ready to run unit tests. 
It is not intended to be a full description of the test features that you can 
use to test your application. Those details can be found in the documentation. 

## Requirements

It is recommended to use the latest version of PHPUnit. At the time of this 
writing we are running version 9.x. Support for this has been built into the 
**composer.json** file that ships with CodeIgniter and can easily be installed 
via [Composer](https://getcomposer.org/) if you don't already have it installed globally.

	> composer install

If running under OS X or Linux, you can create a symbolic link to make running tests a touch nicer.

	> ln -s ./vendor/bin/phpunit ./phpunit

You also need to install [XDebug](https://xdebug.org/index.php) in order
for code coverage to be calculated successfully.

## Setting Up

A number of the tests use a running database. 
In order to set up the database edit the details for the `tests` group in 
**app/Config/Database.php** or **phpunit.xml**. Make sure that you provide a database engine 
that is currently running on your machine. More details on a test database setup are in the 
[Testing Your Database](https://codeigniter.com/user_guide/testing/database.html) section of the documentation.

If you want to run the tests without using live database you can 
exclude `@DatabaseLive` group. Or make a copy of **phpunit.dist.xml** - 
call it **phpunit.xml** - and comment out the `<testsuite>` named `Database`. This will make
the tests run quite a bit faster.

## Running the tests

The entire test suite can be run by simply typing one command-line command from the main directory.

	> ./phpunit

You can limit tests to those within a single test directory by specifying the 
directory name after phpunit. All core tests are stored under **tests/system**.

	> ./phpunit tests/system/HTTP/

Individual tests can be run by including the relative path to the test file.

	> ./phpunit tests/system/HTTP/RequestTest.php

You can run the tests without running the live database and the live cache tests.

	> ./phpunit --exclude-group DatabaseLive,CacheLive

## Generating Code Coverage

To generate coverage information, including HTML reports you can view in your browser, 
you can use the following command: 

	> ./phpunit --colors --coverage-text=tests/coverage.txt --coverage-html=tests/coverage/ -d memory_limit=1024m

This runs all of the tests again collecting information about how many lines, 
functions, and files are tested. It also reports the percentage of the code that is covered by tests. 
It is collected in two formats: a simple text file that provides an overview as well 
as a comprehensive collection of HTML files that show the status of every line of code in the project. 

The text file can be found at **tests/coverage.txt**. 
The HTML files can be viewed by opening **tests/coverage/index.html** in your favorite browser.

## PHPUnit XML Configuration

The repository has a ``phpunit.xml.dist`` file in the project root that's used for
PHPUnit configuration. This is used to provide a default configuration if you
do not have your own configuration file in the project root.

The normal practice would be to copy ``phpunit.xml.dist`` to ``phpunit.xml``
(which is git ignored), and to tailor it as you see fit.
For instance, you might wish to exclude database tests, or automatically generate 
HTML code coverage reports.
