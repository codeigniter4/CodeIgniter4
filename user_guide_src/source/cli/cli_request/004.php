<?php

// command line: php index.php users 21 profile --foo bar --option baz --option qux

echo $request->getOption('foo');       // bar
echo $request->getOption('not-there'); // null
echo $request->getOption('option');    // qux
