# CodeIgniter 4 Development

[![Build Status](https://travis-ci.org/lonnieezell/CodeIgniter4.svg?branch=develop)](https://travis-ci.org/lonnieezell/CodeIgniter4)

This repo contains work that may or may not make it into the official version of CodeIgniter. 

More information about the plans for version 4 can be found in [the announcement](http://forum.codeigniter.com/thread-62615.html) on the forums.

## Contributing
We are not accepting contributions from the public until a stable enough base has been formed, and our plans fleshed out and things settle down a little bit. At that point, we will welcome your comments and help creating the best framework for our community.

## Server Requirements
PHP version 7 is required. 

You can obtain a working Vagrant box from Rasmus Lerdorf [here](https://github.com/rlerdorf/php7dev).

## Tests
Tests are done using the latest version of PHPUnit, which is currently 4.8.x. It is easiest to [install it globally using Composer](https://phpunit.de/manual/current/en/installation.html#installation.composer). Tests are restricted to classes within the `/system` directory, and the directory structure should of the tests should mimic the directory structure of `/system`.

### Running Tests
Tests are run from the command line from the project root - the same folder that has the `phpunit.xml` file in it. 

	$ phpunit

Simply calling this will run the entire test suite. 

### Checking Code Coverage
Code Coverage requires that XDebug is also install and running in your current PHP setup.  The following command will run coverage, provide a coverage report on screen, in a text file (coverage.txt) and within the /coverage directory.

	$ phpunit --colors --coverage-text=coverage.txt --coverage-html=coverage/