<?php

// URI = http://example.com/users/15/profile

// will print 'profile'
echo $uri->getSegment(3, 'foo');
// will print 'bar'
echo $uri->getSegment(4, 'bar');
// will throw an exception
echo $uri->getSegment(5, 'baz');
// will print 'baz'
echo $uri->setSilent()->getSegment(5, 'baz');
// will print '' (empty string)
echo $uri->setSilent()->getSegment(5);
