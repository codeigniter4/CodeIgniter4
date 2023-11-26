<?php

if (! $foo = cache('foo')) {
    echo 'Saving to the cache!<br>';
    $foo = 'foobarbaz!';

    // Save into the cache for 5 minutes
    cache()->save('foo', $foo, 300);
}

echo $foo;
