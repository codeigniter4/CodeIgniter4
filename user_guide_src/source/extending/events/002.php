<?php

use CodeIgniter\Events\Events;

// Call a standalone function
Events::on('pre_system', 'some_function');

// Call on an instance method
$user = new \App\Libraries\User();
Events::on('pre_system', [$user, 'someMethod']);

// Call on a static method
Events::on('pre_system', 'SomeClass::someMethod');

// Use a Closure
Events::on('pre_system', static function (...$params) {
    // ...
});
