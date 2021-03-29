######################
PHP Coding Style Guide
######################

The following document declares a set of coding convention rules to be
followed when contributing PHP code to the CodeIgniter project.

Some of these rules, like naming conventions for example, *may* be
incorporated into the framework's logic and therefore be functionally
enforced (which would be separately documented), but while we would
recommend it, there's no requirement that you follow these conventions in
your own applications.

The `PHP Interop Group <http://www.php-fig.org/>`_ has proposed a number of
canonical recommendations for PHP code style. CodeIgniter is not a member of
of PHP-FIG. We commend their efforts to unite the PHP community,
but no not agree with all of their recommendations.

PSR-2 is PHP-FIG's Coding Style Guide. We do not claim conformance with it,
although there are a lot of similarities. The differences will be pointed out
below.

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED",  "MAY", and "OPTIONAL" in this document are to
be interpreted as described in `RFC 2119 <http://www.ietf.org/rfc/rfc2119.txt>`_.

*Note: When used below, the term "class" refers to all kinds of classes,
interfaces and traits.*

*****
Files
*****

Formatting
==========

- Files MUST use UTF-8 character set encoding without BOM.
- Files MUST use UNIX line endings (LF: `\n`).
- Files MUST end with a single empty line (i.e. LF: `\n`).

Structure
=========

- A single file SHOULD NOT declare more than one class.
  Examples where we feel that more than one class in a source file
  is appropriate:

    -   `system/Debug/CustomExceptions` contains a number of CodeIgniter
        exceptions and errors, that we want to use for a consistent
        experience across applications.
        If we stick with the purist route, then each of the 13+/- custom
        exceptions would require an additional file, which would have a
        performance impact at times.
    -   `system/HTTP/Response` provides a RedirectException, used with the
        Response class.
    -   `system/Router/Router` similarly provides a RedirectException, used with
        the Router class.

- Files SHOULD either declare symbols (i.e. classes, functions, constants)
  or execute non-declarative logic, but SHOULD NOT do both.

Naming
======

- File names MUST end with a ".php" name extension and MUST NOT have
  multiple name extensions.
- Files declaring classes, interfaces or traits MUST have names exactly matching
  the classes that they declare (obviously excluding the ".php" name extension).
- Files declaring functions SHOULD be named in *snake_case.php*.

*************************************
Whitespace, indentation and alignment
*************************************

- Best practice: indentation SHOULD use only tabs.
- Best practice: alignment SHOULD use only spaces.
- If using tabs for anything, you MUST set the tab spacing to 4.

This will accommodate the widest range of developer environment options,
while maintaining consistency of code appearance.

Following the "best practice" above,
the following code block would have a single tab at the beginning of
each line containing braces, and two tabs at the beginning of the
nested statements. No alignment is implied::

    {
        $first = 1;
        $second = 2;
        $third = 3;
    }

Following the "best practice" above,
the following code block would use spaces to have the assignment
operators line up with each other::

    {
        $first  = 1;
        $second = 2;
        $third  = 3;
    }

.. note:: Our indenting and alignment convention differs from PSR-2, which
    **only** uses spaces for both indenting and alignment.

- Unnecessary whitespace characters MUST NOT be present anywhere within a
  script.

  That includes trailing whitespace after a line of code, two or
  more spaces used when only one is necessary (excluding alignment), as
  well as any other whitespace usage that is not functionally required or
  explicitly described in this document.

.. note:: With conforming tab settings, alignment spacing should
    be preserved in all development environments.
    A pull request that deals only with tabs or spaces for alignment
    will not be favorably considered.

****
Code
****

PHP tags
========

- Opening tags MUST only use the `<?php` and `<?=` forms.

  - Scripts producing output SHOULD use the "short echo" `<?=` tag.
  - Scripts declaring and/or using conditional logic SHOULD use the "long"
    `<?php` tag.

- Closing `?>` tags SHOULD NOT be used, unless the intention is to start
  direct output.

  - Scripts that don't produce output MUST NOT use the closing `?>` tag.

Namespaces and classes
======================

- Class names and namespaces SHOULD be declared in `UpperCamelCase`,
  also called `StudlyCaps`, unless
  another form is *functionally* required.

  - Abbreviations in namespaces, class names and method names SHOULD be
    written in capital letters (e.g. PHP).

- Class constants MUST be declared in `CAPITALS_SEPARATED_BY_UNDERSCORES`.
- Class methods, property names and other variables MUST be declared in
  `lowerCamelCase()`.
- Class methods and properties MUST have visibility declarations (i.e.
  `public`, `private` or `protected`).

Methods
-------

To maintain consistency between core classes, class properties MUST
be private or protected, and the following public methods
MUST be used for each such property "x"

- `getX()` when the method returns returns a property value, or null if not set
- `setX(value)` changes a property value, doesn't return anything, and can
  throw exceptions
- `hasX()` returns a boolean to if a property exists
- `newX()` creates an instance of a/the component object and returns it,
  and can throw exceptions
- `isX()` returns true/false for boolean properties

- Methods SHOULD use type hints and return type hints

Procedural code
===============

- Function and variable names SHOULD be declared in `snake_case()` (all
  lowercase letters, separated by underscores), unless another form is
  *functionally* required.
- Constants MUST be declared in `CAPITALS_SEPARATED_BY_UNDERSCORES`.

Keywords
========

- All keywords MUST be written in lowercase letters. This includes "scalar"
  types, but does NOT include core PHP classes such as `stdClass` or
  `Exception`.
- Adjacent keywords are separated by a single space character.
- The keywords `require`, `require_once`, `include`, `include_once` MUST
  be followed by a single space character and MUST NOT be followed by a
  parenthesis anywhere within the declaration.
- The `function` keyword MUST be immediately followed by either an opening
  parenthesis or a single space and a function name.
- Other keywords not explicitly mentioned in this section MUST be separated
  by a single space character from any printable characters around them and
  on the same line.

Operators
=========

- The single dot concatenation, incrementing, decrementing, error
  suppression operators and references MUST NOT be separated from their
  subjects.
- Other operators not explicitly mentioned in this section MUST be
  separated by a single space character from any printable characters
  around them and on the same line.
- An operator MUST NOT be the last set of printable characters on a line.
- An operator MAY be the first set of printable characters on a line.

Logical Operators
=================

-   Use the symbol versions (**||** and **&&**) of the logical operators
    instead of the word versions (**OR** and **AND**).

        -   This is consistent with other programming languages
        -   It avoids the problem of the assignment operator (**=**) having
            higher precedence::

                $result = true && false; // $result is false, expected
                $result = true AND false; // $result is true, evaluated as "($result = true) AND false"
                $result = (true AND false); // $result is false

-   The logical negation operator MUST be separated from its argument by a
    single space, as in **! $result** instead of **!$result**
-   If there is potential confusion with a logical expression, then use
    parentheses for clarity, as shown above.

Control Structures
==================

-   Control structures, such as **if/else** statements, **for/foreach** statements, or
    **while/do** statements, MUST use a brace-surrounded block for their body
    segments.

    Good control structure examples::

        if ( $foo )
        {
            $bar += $baz;
        }
        else
        {
            $baz = 'bar';
        }

    Not-acceptable control structures::

        if ( $foo ) $bar = $oneThing + $anotherThing + $yetAnotherThing + $evenMore;

        if ( $foo ) $bar += $baz;
        else $baz = 'bar';

Docblocks
=========

We use phpDocumentor (phpdoc) to generate the API docs, for all of the source
code inside the `system` folder.

It wants to see a file summary docblock at the top of a PHP file,
before any PHP statements, and then a docblock before each documentable
component, namely any class/interface/trait, and all public and protected
methods/functions/variables. The docblock for a method or function
is expected to describe the parameters, return value, and any exceptions
thrown.

Deviations from the above are considered errors by phpdoc.

An example::

    <?php

    /**
     * CodeIgniter
     *
     * An open source application development framework for PHP
     *
     ...
     *
     * @package    CodeIgniter
     * @author     CodeIgniter Dev Team
     * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
     * @license    https://opensource.org/licenses/MIT	MIT License
     * @link       https://codeigniter.com
     * @since      Version 4.0.0
     * @filesource
     */
    namespace CodeIgniter\Fruit;
    use CodeIgniter\Config\BaseConfig;

    /**
     * Base class for entities in the CodeIgniter\Fruit module.
     *
     * @property $group
     * @property $name
     * @property $description
     *
     * @package CodeIgniter\Fruit
     */
    abstract class BaseFruit
    {

            /**
             * The group a fruit belongs to.
             *
             * @var string
             */
            protected $group;

            /**
             * Fruit constructor.
             *
             * @param BaseConfig       $config
             */
            public function __construct(BaseConfig $Config)
            {
                    $this->group   = 'Unknown';
            }

            //--------------------------------------------------------------------

            /**
             * Model a fruit ripening over time.
             * 
             * @param	array	$params
             */
            abstract public function ripen(array $params);
    }

Other
=====

- Argument separators (comma: `,`) MUST NOT be preceded by a whitespace
  character and MUST be followed by a space character or a newline
  (LF: `\n`).
- Semi-colons (i.e. `;`) MUST NOT be preceded by a whitespace character
  and MUST be followed by a newline (LF: `\n`).

- Opening parentheses SHOULD NOT be followed by a space character.
- Closing parentheses SHOULD NOT be preceded by a space character.

- Opening square brackets SHOULD NOT be followed by a space character,
  unless when using the "short array" declaration syntax.
- Closing square brackets SHOULD NOT be preceded by a space character,
  unless when using the "short array" declaration syntax.

- A curly brace SHOULD be the only printable character on a line, unless:

  - When declaring an anonymous function.
  - Inside a "variable variable" (i.e. `${$foo}` or `${'foo'.$bar}`).
  - Around a variable in a double-quoted string (i.e. `"Foo {$bar}"`).

.. note:: Our control structures braces convention differs from PSR-2.
    We use "Allman style" notation instead.
