<?php

use App\Libraries\Foo;

// Create an instance of the class to test
$obj = new Foo();

// or anonymous class
// $obj = new class () extends Foo {};

// Test the value from Foo
$this->assertEquals('bar', $this->getPrivateProperty($obj, 'baz'));
