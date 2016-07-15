# Kint - debugging helper for PHP developers

[![Total Downloads](https://poser.pugx.org/raveren/kint/downloads.png)](https://packagist.org/packages/raveren/kint)

> **New version** v1.0.0 is released with more than two years of active development - changes are too numerous to list, but there's CLI output and literally hundreds of improvements and additions.

![Screenshot](http://raveren.github.com/kint/img/preview.png)

## What am I looking at?

At first glance Kint is just a pretty replacement for **[var_dump()](http://php.net/manual/en/function.var-dump.php)**, **[print_r()](http://php.net/manual/en/function.print-r.php)** and **[debug_backtrace()](http://php.net/manual/en/function.debug-backtrace.php)**. 

However, it's much, *much* more than that. Even the excellent `xdebug` var_dump improvements don't come close - you will eventually wonder how you developed without it. 

Just to list some of the most useful features:

 * The **variable name and place in code** where Kint was called from is displayed;
 * You can **disable all Kint output easily and on the fly** - so you can even debug live systems without anyone knowing (even though you know you shouldn't be doing that!:). 
 * **CLI is detected** and formatted for automatically (but everything can be overridden on the fly) - if your setup supports it, the output is colored too:
  ![Kint CLI output](http://i.imgur.com/6B9MCLw.png)
 * **Debug backtraces** are finally fully readable, actually informative and a pleasure to the eye.
 * Kint has been **in active development for more than six years** and is shipped with [Drupal 8](https://www.drupal.org/) by default as part of its devel suite. You can trust it not being abandoned or getting left behind in features.
 * Variable content is **displayed in the most informative way** - and you *never, ever* miss anything! Kint guarantees you see every piece of physically available information about everything you are dumping*; 
   * <sup>in some cases, the content is truncated where it would otherwise be too large to view anyway - but the user is always made aware of that;</sup>
 * Some variable content types have an alternative display - for example you will be able see `JSON` in its raw form - but also as an associative array:
  ![Kint displays data intelligently](http://i.imgur.com/9P57Ror.png)
  There are more than ten custom variable type displays inbuilt and more are added periodically.


## Installation and Usage

One of the main goals of Kint is to be **zero setup**. 

[Download the archive](https://github.com/raveren/kint/archive/master.zip) and simply
```php
<?php
require '/kint/Kint.class.php';
```

**Or, if you use Composer:**

```json
"require": {
   "raveren/kint": "^1.0"
}
```

Or just run `composer require raveren/kint`

**That's it, you can now use Kint to debug your code:**

```php
########## DUMP VARIABLE ###########################
Kint::dump($GLOBALS, $_SERVER); // pass any number of parameters

// or simply use d() as a shorthand:
d($_SERVER);


########## DEBUG BACKTRACE #########################
Kint::trace();
// or via shorthand:
d(1);


############# BASIC OUTPUT #########################
# this will show a basic javascript-free display
s($GLOBALS);


######### WHITESPACE FORMATTED OUTPUT ##############
# this will be garbled if viewed in browser as it is whitespace-formatted only
~d($GLOBALS); // just prepend with the tilde


########## MISCELLANEOUS ###########################
# this will disable kint completely
Kint::enabled(false);

ddd('Get off my lawn!'); // no effect

Kint::enabled(true);
ddd( 'this line will stop the execution flow because Kint was just re-enabled above!' );


```

Note, that Kint *does* have configuration (like themes and IDE integration!), but it's in need of being rewritten, so I'm not documenting it yet.

## Tips & Tricks

  * Kint is enabled by default, call `Kint::enabled(false);` to turn its funcionality completely off. The *best practice* is to enable Kint in DEVELOPMENT environment only (or for example `Kint::enabled($_SERVER['REMOTE_ADDR'] === '<your IP>');`) - so even if you accidentally leave a dump in production, no one will know.
  * `sd()` and `ddd()` are shorthands for `s();die;` and `d();die;` respectively. 
    * **Important:** The older shorthand `dd()` is deprecated due to compatibility issues and will eventually be removed. Use the analogous `ddd()` instead.
  * When looking at Kint output, press <kbd>D</kbd> on the keyboard and you will be able to traverse the tree with arrows and tab keys - and expand/collapse nodes with space or enter.
  * Double clicking the `[+]` sign in the output will expand/collapse ALL nodes; triple clicking big blocks of text will select it all.
  * Clicking the tiny arrows on the right of the output open it in a separate window where you can keep it for comparison.
  * To catch output from Kint just assign it to a variable<sup>beta</sup>
```php
$o = Kint::dump($GLOBALS); 
// yes, the assignment is automatically detected, and $o 
// now holds whatever was going to be printed otherwise.

// it also supports modifiers (read on) for the variable:
~$o = Kint::dump($GLOBALS); // this output will be in whitespace
```
  * There are a couple of real-time modifiers you can use:
    * `~d($var)` this call will output in plain text format.
    * `+d($var)` will disregard depth level limits and output everything (careful, this can hang your browser on huge objects)
    * `!d($var)` will show expanded rich output.
    * `-d($var)` will attempt to `ob_clean` the previous output so if you're dumping something inside a HTML page, you will still see Kint output.
  You can combine modifiers too: `~+d($var)`
  * To force a specific dump output type just pass it to the `Kint::enabled()` method. Available options are: `Kint::MODE_RICH` (default), `Kint::MODE_PLAIN`, `Kint::MODE_WHITESPACE` and `Kint::MODE_CLI`:
```php
Kint::enabled(Kint::MODE_WHITESPACE);
$kintOutput = Kint::dump($GLOBALS); 
// now $kintOutput can be written to a text log file and 
// be perfectly readable from there
```
  * To change display theme, use `Kint::$theme = '<theme name>';` where available options are: `'original'` (default), `'solarized'`, `'solarized-dark'` and `'aante-light'`. Here's an (outdated) preview:
  ![Kint themes](http://raveren.github.io/kint/img/theme-preview.png)
  * Kint also includes a naïve profiler you may find handy. It's for determining relatively which code blocks take longer than others:
```php
Kint::dump( microtime() ); // just pass microtime()
sleep( 1 );
Kint::dump( microtime(), 'after sleep(1)' );
sleep( 2 );
ddd( microtime(), 'final call, after sleep(2)' );
```
  ![Kint profiling feature](http://i.imgur.com/tmHUMW4.png)
----

[Visit the project page](http://raveren.github.com/kint/) for documentation, configuration, and more advanced usage examples.

### Author

**Rokas Šleinius** (Raveren)

### License

Licensed under the MIT License
