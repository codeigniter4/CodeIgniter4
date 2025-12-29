<?php

// Set session data
$testSession->set('framework', 'CodeIgniter4');

// Retrieve session data
echo $testSession->get('framework'); // outputs 'CodeIgniter4'

// Remove session data
$testSession->remove('framework');
