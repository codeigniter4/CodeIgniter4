<?php

// URI: http://example.com/one/two

// Before:
$uri->setSegment(4, 'three');
// The URI will be http://example.com/one/two/three

// After:
$uri->setSegment(4, 'three'); // Will throw Exception
$uri->setSegment(3, 'three');
// The URI will be http://example.com/one/two/three
