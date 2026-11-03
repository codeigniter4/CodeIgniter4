<?php

// URI = http://example.com/users/15/profile

// Prints '15'
if ($uri->getSegment(1) === 'users') {
    echo $uri->getSegment(2);
}
