<?php

$atts = [
    'width'       => 800,
    'height'      => 600,
    'scrollbars'  => 'yes',
    'status'      => 'yes',
    'resizable'   => 'yes',
    'screenx'     => 0,
    'screeny'     => 0,
    'window_name' => '_blank',
];

echo anchor_popup('news/local/123', 'Click Me!', $atts);
