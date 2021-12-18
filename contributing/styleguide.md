# CodeIgniter Coding Style Guide

This document declares a set of coding conventions and rules to be followed when contributing PHP code
to the CodeIgniter project.

**Note:**
> While we would recommend it, there's no requirement that you follow these conventions and rules in your
own projects. Usage is discretionary within your projects but strictly enforceable within the framework.

We follow the [PSR-12: Extended Coding Style][psr12] plus a set of our own
styling conventions.

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT", "RECOMMENDED",
"MAY", and "OPTIONAL" in this document are to be interpreted as described in [RFC 2119](http://tools.ietf.org/html/rfc2119).

_Portions of the following rules are from and attributed to [PSR-12][psr12]. Even if we do not copy all the rules to this coding style guide explicitly, such uncopied rules SHALL still apply._

[psr12]: https://www.php-fig.org/psr/psr-12/

## Implementation

Our team uses [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to apply coding standard fixes automatically. If you would like to leverage these tools yourself visit the [Official CodeIgniter Coding Standard](https://github.com/CodeIgniter/coding-standard) repository for details.

## General

### Files

- All PHP files MUST use the Unix LF (linefeed) line ending only.
- All PHP files MUST end with a non-blank line, terminated with a single LF.
- The closing `?>` tag MUST be omitted from files containing only PHP.

### Lines

- There MUST NOT be a hard limit on line length.
- The soft limit on line length MUST be 120 characters.
- Lines SHOULD NOT be longer than 80 characters; lines longer than that SHOULD be split into multiple subsequent lines of no more than 80 characters each.
- There MUST NOT be trailing whitespace at the end of lines.
- Blank lines MAY be added to improve readability and to indicate related blocks of code except where explicitly forbidden.
- There MUST NOT be more than one statement per line.

### Indenting

- Code MUST use an indent of 4 spaces for each indent level, and MUST NOT use tabs for indenting.

### Keywords and Types

- All PHP reserved [keywords][1] and [types][2] MUST be in lower case.
- Any new types and keywords added to future PHP versions MUST be in lower case.
- Short form of type keywords MUST be used i.e. `bool` instead of `boolean`, `int` instead of `integer` etc.

[1]: http://php.net/manual/en/reserved.keywords.php
[2]: http://php.net/manual/en/reserved.other-reserved-words.php

## Declare Statements, Namespace, and Import Statements

The header of a PHP file may consist of a number of different blocks. If present, each of the blocks below
MUST be separated by a single blank line, and MUST NOT contain a blank line. Each block MUST be in the order
listed below, although blocks that are not relevant may be omitted.

- Opening `<?php` tag.
- File-level docblock.
- One or more declare statements.
- The namespace declaration of the file.
- One or more class-based `use` import statements.
- One or more function-based `use` import statements.
- One or more constant-based `use` import statements.
- The remainder of the code in the file.

When a file contains a mix of HTML and PHP, any of the above sections may still be used. If so, they MUST be
present at the top of the file, even if the remainder of the code consists of a closing PHP tag and then a
mixture of HTML and PHP.

When the opening `<?php` tag is on the first line of the file, it MUST be on its own line with no other
statements unless it is a file containing markup outside of PHP opening and closing tags.

Import statements MUST never begin with a leading backslash as they must always be fully qualified.

## Classes, Properties, and Methods

The term "class" refers to all classes, interfaces, and traits.

- Any closing brace MUST NOT be followed by any comment or statement on the same line.
- When instantiating a new class, parentheses MUST always be present even when there are no arguments passed to the constructor.

### Extends and Implements

- The `extends` and `implements` keywords MUST be declared on the same line as the class name.
- Lists of `implements` and, in the case of interfaces, `extends` MAY be split across multiple lines,
	where each subsequent line is indented once. When doing so, the first item in the list MUST be on
	the next line, and there MUST be only one interface per line.

- The opening brace for the class MUST go on its own line; the closing brace for the class MUST go on the next line after the body.
- Opening braces MUST be on their own line and MUST NOT be preceded or followed by a blank line.
- Closing braces MUST be on their own line and MUST NOT be preceded by a blank line.

### Using traits

- The `use` keyword used inside the classes to implement traits MUST be declared on the next line after the opening brace.
- Each individual trait that is imported into a class MUST be included one-per-line and each inclusion MUST have its own use import statement.
- When the class has nothing after the use import statement, the class closing brace MUST be on the next line after the use import statement.
- Otherwise, it MUST have a blank line after the use import statement.
- When using the insteadof and as operators they must be used as follows taking note of indentation, spacing, and new lines.

### Properties and Constants

- Visibility MUST be declared on all properties.
- Visibility MUST be declared on all constants if your project PHP minimum version supports constant visibilities (PHP 7.1 or later).
- The `var` keyword MUST NOT be used to declare a property.
- here MUST NOT be more than one property declared per statement.
- Property names MUST NOT be prefixed with a single underscore to indicate protected or private visibility. That is, an underscore prefix explicitly has no meaning.
- There MUST be a space between type declaration and property name.

### Methods and Functions

- Visibility MUST be declared on all methods.
- Method names MUST NOT be prefixed with a single underscore to indicate protected or private visibility. That is, an underscore prefix explicitly has no meaning.
- Method and function names MUST NOT be declared with space after the method name. The opening brace MUST go on its own line, and the closing brace MUST go on the next line following the body. There MUST NOT be a space after the opening parenthesis, and there MUST NOT be a space before the closing parenthesis.

### Method and Function Arguments

- In the argument list, there MUST NOT be a space before each comma, and there MUST be one space after each comma.
- Argument lists MAY be split across multiple lines, where each subsequent line is indented once. When doing so, the first item in the list MUST be on the next line, and there MUST be only one argument per line.
- When the argument list is split across multiple lines, the closing parenthesis and opening brace MUST be placed together on their own line with one space between them.
- When you have a return type declaration present, there MUST be one space after the colon followed by the type declaration. The colon and declaration MUST be on the same line as the argument list closing parenthesis with no spaces between the two characters.
- In nullable type declarations, there MUST NOT be a space between the question mark and the type.
- When using the reference operator & before an argument, there MUST NOT be a space after it.
- There MUST NOT be a space between the variadic three dot operator and the argument name.
- When combining both the reference operator and the variadic three dot operator, there MUST NOT be any space between the two of them.

### `abstract`, `final`, and `static`

- When present, the `abstract` and `final` declarations MUST precede the visibility declaration.
- When present, the `static` declaration MUST come after the visibility declaration.

### Method and Function Calls

- When making a method or function call, there MUST NOT be a space between the method or function name
and the opening parenthesis, there MUST NOT be a space after the opening parenthesis, and there MUST NOT be a
space before the closing parenthesis. In the argument list, there MUST NOT be a space before each comma, and
there MUST be one space after each comma.
- Argument lists MAY be split across multiple lines, where each subsequent line is indented once. When doing
so, the first item in the list MUST be on the next line, and there MUST be only one argument per line. A
single argument being split across multiple lines (as might be the case with an anonymous function or array)
does not constitute splitting the argument list itself.

## Control Structures

The general style rules for control structures are as follows:

- There MUST be one space after the control structure keyword
- There MUST NOT be a space after the opening parenthesis
- There MUST NOT be a space before the closing parenthesis
- There MUST be one space between the closing parenthesis and the opening brace
- The structure body MUST be indented once
- The body MUST be on the next line after the opening brace
- The closing brace MUST be on the next line after the body

The body of each structure MUST be enclosed by braces. This standardizes how the structures look and reduces the likelihood of introducing errors as new lines get added to the body.

### `if`, `elseif`, `else`

- The keyword `elseif` SHOULD be used instead of else if so that all control keywords look like single words.
- Expressions in parentheses MAY be split across multiple lines, where each subsequent line is indented at
least once. When doing so, the first condition MUST be on the next line. The closing parenthesis and opening
brace MUST be placed together on their own line with one space between them. Boolean operators between
conditions MUST always be at the beginning or at the end of the line, not a mix of both.

### `switch`, `case`

- There MUST be a comment such as `// no break` when fall-through is intentional in a non-empty case body.
- Expressions in parentheses MAY be split across multiple lines, where each subsequent line is indented at
least once. When doing so, the first condition MUST be on the next line. The closing parenthesis and opening
brace MUST be placed together on their own line with one space between them. Boolean operators between
conditions MUST always be at the beginning or at the end of the line, not a mix of both.

### `while`, `do while`

- Expressions in parentheses MAY be split across multiple lines, where each subsequent line is indented at
least once. When doing so, the first condition MUST be on the next line. The closing parenthesis and opening
brace MUST be placed together on their own line with one space between them. Boolean operators between
conditions MUST always be at the beginning or at the end of the line, not a mix of both.

## Operators

- Style rules for operators are grouped by arity (the number of operands they take).
- When space is permitted around an operator, multiple spaces MAY be used for readability purposes.
- All operators not described here are left undefined.

### Unary operators

- The increment/decrement operators MUST NOT have any space between the operator and operand.
- Type casting operators MUST NOT have any space within the parentheses.

### Binary operators

- All binary arithmetic, comparison, assignment, bitwise, logical, string, and type operators MUST be preceded and followed by at least one space.

### Ternary operators

- The conditional operator, also known simply as the ternary operator, MUST be preceded and followed by at least one space around both the `?` and `:` characters.
- When the middle operand of the conditional operator is omitted, the operator MUST follow the same style rules as other binary comparison operators.

**Note:**
> All the preceding rules are quoted from PSR-12. You may visit its website to view the code block samples.

## Custom Conventions

### File Naming

- Files containing PHP code SHOULD end with a ".php" extension.
- Files containing templates SHOULD end with a ".tpl" extension.
- Files containing classes, interfaces, or traits MUST have their base name exactly matching the name
of the classes they declare.
- Files declaring procedural functions SHOULD be written in snake_case format.

### Naming of Structural Elements

- Constants MUST be declared in UPPERCASE_SEPARATED_WITH_UNDERSCORES.
- Class names MUST be declared in PascalCase.
- Method and property names MUST be declared in camelCase.
- Procedural functions MUST be in snake_case.
- Abbreviations/acronyms/initialisms SHOULD be written in their own natural format.

### Logical Operators

- The negation operator `!` SHOULD have one space from its argument.
```diff
-!$result
+! $result
```

- Use parentheses to clarify potentially confusing logical expressions.

### PHP Docblocks (PHPDoc)

- There SHOULD be no useless PHPDoc annotations.
```diff
-/**
- * @param string $data Data
- * @return void
- */
  public function analyse(string $data): void {};
```

### PHPUnit Assertions

- As much as possible, you SHOULD always use the strict version of assertions.
```diff
-$this->assertEquals(12, (int) $axis);
+$this->assertSame(12, (int) $axis);
```

- Use the dedicated assertion instead of using internal types.
```diff
-$this->assertSame(true, is_cli());
+$this->assertTrue(is_cli());

-$this->assertTrue(array_key_exists('foo', $array));
+$this->assertArrayHasKey('foo', $array);
```
