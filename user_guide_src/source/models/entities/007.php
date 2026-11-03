<?php

// Auto-hash the password - both do the same thing
$user->password = 'my great password';
$user->setPassword('my great password');
