# Running Application Tests

This is the quick-start to CodeIgniter testing. Its intent is to describe what 
it takes to set up your application and get it ready to run unit tests. 
It is not intended to be a full description of the test features that you can 
use to test your application. Those details can be found in the documentation.

## Resources
* [CodeIgniter 4 User Guide on Testing](https://codeigniter4.github.io/userguide/testing/index.html)
* [PHPUnit docs](https://phpunit.readthedocs.io/en/8.5/index.html)

## Requirements

It is recommended to use the latest version of PHPUnit. At the time of this 
writing we are running version 8.5.13. Support for this has been built into the 
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
*Docs>>Testing>>Testing Your Database* section of the documentation.

If you want to run the tests without using live database you can 
exclude @DatabaseLive group. Or make a copy of **phpunit.dist.xml** - 
call it **phpunit.xml** - and comment out the <testsuite> named "database". This will make
the tests run quite a bit faster.

## Running the tests

The entire test suite can be run by simply typing one command-line command from the main directory.

	> ./phpunit

You can limit tests to those within a single test directory by specifying the 
directory name after phpunit. 

	> ./phpunit app/Models

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

## Test Cases

Every test needs a *test case*, or class that your tests extend. CodeIgniter 4
provides a few that you may use directly:
* `CodeIgniter\Test\CIUnitTestCase` - for basic tests with no other service needs
* `CodeIgniter\Test\DatabaseTestTrait` - for tests that need database access

Most of the time you will want to write your own test cases to hold functions and services
common to your test suites.

## Creating Tests

All tests go in the **tests/** directory. Each test file is a class that extends a
**Test Case** (see above) and contains methods for the individual tests. These method
names must start with the word "test" and should have descriptive names for precisely what
they are testing:
`testUserCanModifyFile()` `testOutputColorMatchesInput()` `testIsLoggedInFailsWithInvalidUser()`

Writing tests is an art, and there are many resources available to help learn how.
Review the links above and always pay attention to your code coverage.

### Database Tests

Tests can include migrating, seeding, and testing against a mock or live<sup>1</sup> database.
Be sure to modify the test case (or create your own) to point to your seed and migrations
and include any additional steps to be run before tests in the `setUp()` method.

<sup>1</sup> Note: If you are using database tests that require a live database connection
you will need to rename **phpunit.xml.dist** to **phpunit.xml**, uncomment the database
configuration lines and add your connection details. Prevent **phpunit.xml** from being
tracked in your repo by adding it to **.gitignore**.
