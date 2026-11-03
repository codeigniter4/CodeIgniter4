<?php

// Displays "The time is now 23:21:28 GMT-5"
echo lang('Test.longTime', [time()], 'ru-RU');

// Displays "£7.41"
echo lang('{price, number, currency}', ['price' => 7.41], 'en-GB');
// Displays "$7.41"
echo lang('{price, number, currency}', ['price' => 7.41], 'en-US');
