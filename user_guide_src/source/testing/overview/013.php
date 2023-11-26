<?php

// Create an instance of the class to test
$obj = new Foo();

// Get the invoker for the 'privateMethod' method.
$method = $this->getPrivateMethodInvoker($obj, 'privateMethod');

// Test the results
$this->assertEquals('bar', $method('param1', 'param2'));
