<?php

// command line: php index.php users 21 profile --foo bar --option baz --option qux

echo $request->getRawOption('foo');         // bar
echo $request->getRawOption('not-there');   // null
var_dump($request->getRawOption('option')); // array(2) { [0]=> string(3) "baz" [1]=> string(3) "qux" }
