######################
PHP Coding style guide
######################

The following document declares a set of coding convention rules to be
followed when contributing PHP code to the CodeIgniter project.

Some of these rules, like naming conventions for example, *may* be
incorporated into the framework's logic and therefore be functionally
enforced (which would be separately documented), but while we would
recommend it, there's no requirement that you follow these conventions in
your own applications.

TODO: Add acknowledgement notice in the footer, referencing popular
      coding style conventions that we've derived content from.

*The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED",  "MAY", and "OPTIONAL" in this document are to
be interpreted as described in `RFC 2119 <http://www.ietf.org/rfc/rfc2119.txt>`_.*

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

TODO: Add (links for?) instructions to configure popular IDEs and plain-text editors.

Structure
=========

- A single file SHOULD NOT declare more than one class.
- Files SHOULD either declare symbols (i.e. classes, functions, constants)
  or execute non-declarative logic, but SHOULD NOT do both.

Naming
======

- File names MUST end with a ".php" name extension and MUST NOT have
  multiple name extensions.
- A file that declares a class (Files declaring classes MUST have names
  exactly matching the classes that they declare (obviously excluding
  the ".php" name extension).
- Files declaring functions SHOULD be named in *snake_case.php*.

*************************************
Whitespace, indentation and alignment
*************************************

- Indentation MUST use only tabs.
- Alignment MUST use only spaces.

TODO: Add examples for indentation vs. alignment.

- Unnecessary whitespace characters MUST NOT be present anywhere within a
  script.

  That includes trailing whitespace after a line of code, two or
  more spaces used when only one is necessary (excluding alignment), as
  well as any other whitespace usage that is not functionally required or
  explicitly described in this document.

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

- Class names and namespaces SHOULD be declared in `UpperCamelCase`, unless
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

To maintain consistency between core classes the following method names
MUST be used where applicable:

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

Formatting
==========

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

Other
=====

- Argument separators (comma: `,`) MUST NOT be preceeded by a whitespace
  character and MUST be followed by a space character or a newline
  (LF: `\n`).
- Semi-colons (i.e. `;`) MUST NOT be preceeded by a whitespace character
  and MUST be followed by a newline (LF: `\n`).

- Opening parentheses SHOULD NOT be followed by a space character.
- Closing parentheses SHOULD NOT be preceeded by a space character.

- Opening square brackets SHOULD NOT be followed by a space character,
  unless when using the "short array" declaration syntax.
- Closing square backets SHOULD NOT be followed by a space character,
  unless when using the "short array" declaration syntax.

- A curly brace SHOULD be the only printable character on a line, unless:

  - When declaring an anonymous function.
  - Inside a "variable variable" (i.e. `${$foo}` or `${'foo'.$bar}`).
  - Around a variable in a double-quoted string (i.e. `"Foo {$bar}"`).