<?php

$temp      = '';
$template1 = '<li><a href="{link}">{title}</a></li>';
$data1     = [
    ['title' => 'First Link', 'link' => '/first'],
    ['title' => 'Second Link', 'link' => '/second'],
];

foreach ($data1 as $menuItem) {
    $temp .= $parser->setData($menuItem)->renderString($template1);
}

$template2 = '<ul>{menuitems}</ul>';
$data      = [
    'menuitems' => $temp,
];

return $parser->setData($data)->renderString($template2);
