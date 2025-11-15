<?php

// The language file, Tests.php
return [
    'shortTime'  => 'The time is now {0, time, short}.',
    'mediumTime' => 'The time is now {0, time, medium}.',
    'longTime'   => 'The time is now {0, time, long}.',
    'fullTime'   => 'The time is now {0, time, full}.',
    'shortDate'  => 'The date is now {0, date, short}.',
    'mediumDate' => 'The date is now {0, date, medium}.',
    'longDate'   => 'The date is now {0, date, long}.',
    'fullDate'   => 'The date is now {0, date, full}.',
    'spelledOut' => '34 is {0, spellout}',
    'ordinal'    => 'The ordinal is {0, ordinal}',
    'duration'   => 'It has been {0, duration}',
];

// Displays "The time is now 11:18 PM"
echo lang('Tests.shortTime', [time()]);
// Displays "The time is now 11:18:50 PM"
echo lang('Tests.mediumTime', [time()]);
// Displays "The time is now 11:19:09 PM CDT"
echo lang('Tests.longTime', [time()]);
// Displays "The time is now 11:19:26 PM Central Daylight Time"
echo lang('Tests.fullTime', [time()]);

// Displays "The date is now 8/14/16"
echo lang('Tests.shortDate', [time()]);
// Displays "The date is now Aug 14, 2016"
echo lang('Tests.mediumDate', [time()]);
// Displays "The date is now August 14, 2016"
echo lang('Tests.longDate', [time()]);
// Displays "The date is now Sunday, August 14, 2016"
echo lang('Tests.fullDate', [time()]);

// Displays "34 is thirty-four"
echo lang('Tests.spelledOut', [34]);

// Displays "It has been 408,676:24:35"
echo lang('Tests.ordinal', [time()]);
