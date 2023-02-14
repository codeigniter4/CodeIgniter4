<?php

// URI = http://example.com/users/15/profile

echo $uri->getSegment(1, 'foo');
// will print 'users'

echo $uri->getSegment(3, 'bar');
// will print 'profile'

echo $uri->getSegment(4, 'baz');
// will throw an exception

echo $uri->setSilent()->getSegment(4, 'baz');
// will print 'baz'

echo $uri->setSilent()->getSegment(4);
// will print '' (empty string)
