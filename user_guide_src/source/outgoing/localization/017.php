<?php

// Language/en/Fruit.php

return [
    'list' => [
        'Apples',
        'Bananas',
        'Grapes',
        'Lemons',
        'Oranges',
        'Strawberries',
    ],
];

// Displays "Apples, Bananas, Grapes, Lemons, Oranges, Strawberries"
echo implode(', ', lang('Fruit.list'));
