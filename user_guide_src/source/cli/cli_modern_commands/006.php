<?php

// Inside execute():

// Call another modern command with positional arguments...
$this->call('cache:clear', arguments: ['file']);

// ...and/or with options. Use `null` for a flag's value to model "the flag was passed".
$this->call('logs:clear', options: ['force' => null]);
