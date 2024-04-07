<?php

// Controllers/Translation/Lang.php
$message  = lang('Text.info.success');
$message2 = lang('Text.paragraph');

// The following will be saved in Language/en/Text.php
return [
    'info' => [
        'success' => 'Text.info.success',
    ],
    'paragraph' => 'Text.paragraph',
];
