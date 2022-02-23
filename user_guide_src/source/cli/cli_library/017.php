<?php

$titles = [
    'task1a',
    'task1abc',
];
$descriptions = [
    'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    "Lorem Ipsum has been the industry's standard dummy text ever since the",
];

// Determine the maximum length of all titles
// to determine the width of the left column
$maxlen = max(array_map('strlen', $titles));

for ($i = 0; $i < count($titles); $i++) {
    CLI::write(
        // Display the title on the left of the row
        substr(
            $titles[$i] . str_repeat(' ', $maxlen + 3),
            0,
            $maxlen + 3
        ) .
        // Wrap the descriptions in a right-hand column
        // with its left side 3 characters wider than
        // the longest item on the left.
        CLI::wrap($descriptions[$i], 40, $maxlen + 3)
    );
}
