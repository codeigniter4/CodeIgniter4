<?php

// Set a session value
$testSession->set('framework', 'CodeIgniter4');

// Assert the state of the session using PHPUnit assertions
$this->assertSame('CodeIgniter4', $testSession->get('framework')); // Value exists

// Not empty
$this->assertNotEmpty($testSession->get('framework'));

// Remove the value and assert it's gone
$testSession->remove('framework');

// Should be null
$this->assertNull($testSession->get('framework'));
