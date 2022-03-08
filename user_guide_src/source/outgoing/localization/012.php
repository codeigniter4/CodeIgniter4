<?php

// The language file, Tests.php:
return [
    'apples'      => 'I have {0, number} apples.',
    'men'         => 'The top {1, number} men out-performed the remaining {0, number}',
    'namedApples' => 'I have {number_apples, number, integer} apples.',
];

// Displays "I have 3 apples."
echo lang('Tests.apples', [3]);
