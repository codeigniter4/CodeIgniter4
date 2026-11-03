<?php

// command line: php index.php users 21 profile --foo bar
echo $request->getSegments();  // ['users', '21', 'profile']
