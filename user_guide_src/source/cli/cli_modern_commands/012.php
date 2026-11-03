<?php

// Inside execute():

// Run `cache:clear` without leaking its own output; emit our own message instead.
$exitCode = $this->callSilently('cache:clear');

if ($exitCode === EXIT_SUCCESS) {
    $this->getOutput()->write('Cache cleared as part of deploy step.', 'green');
}
