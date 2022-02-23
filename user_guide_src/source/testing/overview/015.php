<?php

// Create an instance of the class to test
$obj = new Foo();

// Test the value
$this->assertEquals('bar', $this->getPrivateProperty($obj, 'baz'));
